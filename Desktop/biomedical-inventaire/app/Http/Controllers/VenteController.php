<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Equipement;
use App\Models\LigneVente;
use App\Models\MouvementStock;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    public function index(Request $request)
    {
        $query = Vente::with(['client', 'user'])->latest('date_vente');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_vente', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_vente', '<=', $request->date_fin);
        }

        $ventes = $query->paginate(15)->withQueryString();

        $stats = [
            'total_mois'      => Vente::whereMonth('date_vente', now()->month)
                ->whereYear('date_vente', now()->year)
                ->whereNotIn('statut', ['annulee', 'brouillon'])
                ->sum('total_ttc'),
            'nb_mois'         => Vente::whereMonth('date_vente', now()->month)
                ->whereYear('date_vente', now()->year)
                ->whereNotIn('statut', ['annulee'])
                ->count(),
            'impayees'        => Vente::impayees()->count(),
            'impayees_montant'=> Vente::impayees()->selectRaw('SUM(total_ttc - montant_paye) as reste')->value('reste') ?? 0,
        ];

        $clients = Client::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');

        return view('ventes.index', compact('ventes', 'stats', 'clients'));
    }

    public function create()
    {
        $clients     = Client::where('statut', 'actif')->orderBy('nom')->get(['id', 'nom', 'code_client', 'type']);
        $equipements = Equipement::with('categorie')
            ->whereNotIn('etat', ['reformé', 'hors_service'])
            ->where('quantite', '>', 0)
            ->orderBy('designation')
            ->get(['id', 'designation', 'code_inventaire', 'quantite', 'prix_achat', 'categorie_id']);

        // Tableau simplifié pour le JS de la vue (évite @json multi-ligne dans Blade)
        $equipementsJs = $equipements->map(function ($e) {
            return [
                'id'          => $e->id,
                'designation' => $e->designation,
                'code'        => $e->code_inventaire,
                'quantite'    => $e->quantite,
                'prix'        => (float) ($e->prix_achat ?? 0),
            ];
        })->values()->toArray();

        $numero = Vente::genererNumero();

        return view('ventes.create', compact('clients', 'equipements', 'equipementsJs', 'numero'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'             => 'required|exists:clients,id',
            'date_vente'            => 'required|date',
            'mode_paiement'         => 'nullable|in:especes,virement,cheque,mobile_money,credit,autre',
            'remise_globale'        => 'nullable|numeric|min:0|max:100',
            'tva'                   => 'nullable|numeric|min:0|max:100',
            'date_livraison_prevue' => 'nullable|date',
            'date_echeance'         => 'nullable|date',
            'notes'                 => 'nullable|string',
            'lignes'                => 'required|array|min:1',
            'lignes.*.equipement_id'     => 'required|exists:equipements,id',
            'lignes.*.quantite'          => 'required|integer|min:1',
            'lignes.*.prix_unitaire_ht'  => 'required|numeric|min:0',
            'lignes.*.remise'            => 'nullable|numeric|min:0|max:100',
        ]);

        // Vérifier stocks avant de commencer
        foreach ($request->lignes as $ligne) {
            $eq = Equipement::find($ligne['equipement_id']);
            if ($eq->quantite < $ligne['quantite']) {
                return back()->withErrors([
                    'lignes' => "Stock insuffisant pour \"{$eq->designation}\" : {$eq->quantite} disponible(s), {$ligne['quantite']} demandé(s).",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            // Créer la vente
            $vente = Vente::create([
                'numero_facture'        => Vente::genererNumero(),
                'client_id'             => $request->client_id,
                'user_id'               => auth()->id(),
                'statut'                => 'confirmee',
                'mode_paiement'         => $request->mode_paiement,
                'date_vente'            => $request->date_vente,
                'date_livraison_prevue' => $request->date_livraison_prevue,
                'date_echeance'         => $request->date_echeance,
                'remise_globale'        => $request->remise_globale ?? 0,
                'tva'                   => $request->tva ?? 19.25,
                'sous_total_ht'         => 0,
                'montant_remise'        => 0,
                'montant_tva'           => 0,
                'total_ttc'             => 0,
                'montant_paye'          => $request->mode_paiement !== 'credit' ? 0 : 0,
                'notes'                 => $request->notes,
            ]);

            // Créer les lignes et débiter le stock
            foreach ($request->lignes as $ligneData) {
                $eq     = Equipement::find($ligneData['equipement_id']);
                $remise = $ligneData['remise'] ?? 0;
                $totalHt = round($ligneData['quantite'] * $ligneData['prix_unitaire_ht'] * (1 - $remise / 100), 2);

                LigneVente::create([
                    'vente_id'             => $vente->id,
                    'equipement_id'        => $eq->id,
                    'designation_snapshot' => $eq->designation,
                    'reference_snapshot'   => $eq->code_inventaire,
                    'quantite'             => $ligneData['quantite'],
                    'prix_unitaire_ht'     => $ligneData['prix_unitaire_ht'],
                    'remise'               => $remise,
                    'total_ht'             => $totalHt,
                ]);

                // Débit du stock
                $ancienneQte = $eq->quantite;
                $nouvelleQte = $ancienneQte - $ligneData['quantite'];
                $eq->update(['quantite' => $nouvelleQte]);

                // Mouvement de stock automatique
                MouvementStock::create([
                    'equipement_id'   => $eq->id,
                    'type'            => 'sortie',
                    'quantite'        => $ligneData['quantite'],
                    'quantite_avant'  => $ancienneQte,
                    'quantite_apres'  => $nouvelleQte,
                    'reference_document' => $vente->numero_facture,
                    'motif'           => "Vente — {$vente->numero_facture}",
                    'user_id'         => auth()->id(),
                    'date_mouvement'  => now(),
                ]);
            }

            // Recalculer les totaux
            $vente->load('lignes');
            $vente->recalculerTotaux();

            // Si paiement immédiat, marquer payé
            if (in_array($request->mode_paiement, ['especes', 'mobile_money', 'virement', 'cheque'])) {
                $vente->update([
                    'montant_paye' => $vente->fresh()->total_ttc,
                    'statut'       => 'payee',
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($vente)
                ->withProperties(['numero' => $vente->numero_facture, 'total_ttc' => $vente->fresh()->total_ttc])
                ->log("Vente créée : {$vente->numero_facture}");

            session(['vente_creee' => $vente->id]);
        });

        $venteId = session()->pull('vente_creee');

        return redirect()->route('ventes.show', $venteId)
            ->with('success', 'Vente enregistrée et stock mis à jour.');
    }

    public function show(Vente $vente)
    {
        $vente->load(['client', 'user', 'lignes.equipement']);

        return view('ventes.show', compact('vente'));
    }

    public function facture(Vente $vente)
    {
        $vente->load(['client', 'user', 'lignes.equipement']);

        return view('ventes.facture', compact('vente'));
    }

    /**
     * Enregistrer un paiement partiel ou total
     */
    public function paiement(Request $request, Vente $vente)
    {
        $request->validate([
            'montant'       => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|in:especes,virement,cheque,mobile_money,autre',
        ]);

        $nouveau = min($vente->montant_paye + $request->montant, $vente->total_ttc);
        $statut  = $nouveau >= $vente->total_ttc ? 'payee' : $vente->statut;

        $vente->update([
            'montant_paye'  => $nouveau,
            'mode_paiement' => $request->mode_paiement,
            'statut'        => $statut,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($vente)
            ->log("Paiement enregistré sur {$vente->numero_facture} : " . number_format($request->montant, 0, ',', ' ') . ' FCFA');

        return back()->with('success', 'Paiement enregistré.');
    }

    /**
     * Annuler une vente et restituer le stock
     */
    public function annuler(Request $request, Vente $vente)
    {
        if ($vente->statut === 'annulee') {
            return back()->withErrors(['statut' => 'Cette vente est déjà annulée.']);
        }

        DB::transaction(function () use ($vente) {
            foreach ($vente->lignes as $ligne) {
                $eq = $ligne->equipement;
                $ancienneQte = $eq->quantite;
                $nouvelleQte = $ancienneQte + $ligne->quantite;

                $eq->update(['quantite' => $nouvelleQte]);

                MouvementStock::create([
                    'equipement_id'   => $eq->id,
                    'type'            => 'retour',
                    'quantite'        => $ligne->quantite,
                    'quantite_avant'  => $ancienneQte,
                    'quantite_apres'  => $nouvelleQte,
                    'reference_document' => $vente->numero_facture,
                    'motif'           => "Annulation vente — {$vente->numero_facture}",
                    'user_id'         => auth()->id(),
                    'date_mouvement'  => now(),
                ]);
            }

            $vente->update(['statut' => 'annulee']);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($vente)
                ->log("Vente annulée : {$vente->numero_facture} — stock restitué");
        });

        return back()->with('success', 'Vente annulée et stock restitué.');
    }

    /**
     * Marquer comme livrée
     */
    public function livrer(Vente $vente)
    {
        if (! in_array($vente->statut, ['confirmee', 'facturee'])) {
            return back()->withErrors(['statut' => 'Impossible de marquer cette vente comme livrée.']);
        }

        $vente->update([
            'statut'                => 'livree',
            'date_livraison_reelle' => now(),
        ]);

        return back()->with('success', 'Vente marquée comme livrée.');
    }
}
