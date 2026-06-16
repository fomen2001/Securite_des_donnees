<?php

namespace App\Http\Controllers\Achats;

use App\Http\Controllers\Controller;
use App\Models\BonCommande;
use App\Models\BonCommandeLigne;
use App\Models\Equipement;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonCommandeController extends Controller
{
    public function index(Request $request)
    {
        $query = BonCommande::with(['fournisseur', 'user'])->latest('date_commande');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_commande', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_commande', '<=', $request->date_fin);
        }

        $commandes = $query->paginate(15)->withQueryString();

        $stats = [
            'total_mois'   => BonCommande::whereMonth('date_commande', now()->month)
                ->whereYear('date_commande', now()->year)
                ->whereNotIn('statut', ['annulee', 'brouillon'])
                ->sum('montant_ttc'),
            'en_attente'   => BonCommande::where('statut', 'confirmee')->count(),
            'partielles'   => BonCommande::where('statut', 'partiellement_recue')->count(),
            'ce_mois'      => BonCommande::whereMonth('date_commande', now()->month)
                ->whereYear('date_commande', now()->year)->count(),
        ];

        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');

        return view('achats.commandes.index', compact('commandes', 'stats', 'fournisseurs'));
    }

    public function create()
    {
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->get(['id', 'nom', 'contact_nom', 'telephone', 'email']);
        $equipements  = Equipement::orderBy('nom')->get(['id', 'nom', 'reference', 'prix_unitaire']);

        return view('achats.commandes.create', compact('fournisseurs', 'equipements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fournisseur_id'           => 'required|exists:fournisseurs,id',
            'date_commande'            => 'required|date',
            'date_livraison_souhaitee' => 'nullable|date|after_or_equal:date_commande',
            'taux_tva'                 => 'required|numeric|min:0|max:100',
            'lignes'                   => 'required|array|min:1',
            'lignes.*.designation'     => 'required|string|max:255',
            'lignes.*.quantite_commandee' => 'required|numeric|min:0.01',
            'lignes.*.prix_unitaire_ht'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $bc = BonCommande::create([
                'numero'                   => BonCommande::genererNumero(),
                'fournisseur_id'           => $request->fournisseur_id,
                'user_id'                  => auth()->id(),
                'date_commande'            => $request->date_commande,
                'date_livraison_souhaitee' => $request->date_livraison_souhaitee,
                'statut'                   => $request->action === 'confirmer' ? 'confirmee' : 'brouillon',
                'taux_tva'                 => $request->taux_tva,
                'conditions'               => $request->conditions,
                'notes'                    => $request->notes,
                'montant_ht'  => 0,
                'montant_tva' => 0,
                'montant_ttc' => 0,
            ]);

            foreach ($request->lignes as $ligne) {
                $ht = round($ligne['quantite_commandee'] * $ligne['prix_unitaire_ht'], 2);
                BonCommandeLigne::create([
                    'bon_commande_id'       => $bc->id,
                    'equipement_id'         => $ligne['equipement_id'] ?? null,
                    'designation'           => $ligne['designation'],
                    'reference_fournisseur' => $ligne['reference_fournisseur'] ?? null,
                    'quantite_commandee'    => $ligne['quantite_commandee'],
                    'quantite_recue'        => 0,
                    'unite'                 => $ligne['unite'] ?? 'unité',
                    'prix_unitaire_ht'      => $ligne['prix_unitaire_ht'],
                    'taux_tva'              => $request->taux_tva,
                    'total_ht'              => $ht,
                ]);
            }

            $bc->load('lignes');
            $bc->recalculerTotaux();
        });

        return redirect()->route('achats.commandes.index')
            ->with('success', 'Bon de commande créé avec succès.');
    }

    public function show(BonCommande $bonCommande)
    {
        $bonCommande->load(['fournisseur', 'user', 'lignes.equipement', 'receptions.lignes']);
        return view('achats.commandes.show', compact('bonCommande'));
    }

    public function confirmer(BonCommande $bonCommande)
    {
        abort_if($bonCommande->statut !== 'brouillon', 403);
        $bonCommande->update(['statut' => 'confirmee']);
        return back()->with('success', 'Bon de commande confirmé.');
    }

    public function annuler(BonCommande $bonCommande)
    {
        abort_if(!in_array($bonCommande->statut, ['brouillon', 'confirmee']), 403);
        $bonCommande->update(['statut' => 'annulee']);
        return back()->with('success', 'Bon de commande annulé.');
    }
}
