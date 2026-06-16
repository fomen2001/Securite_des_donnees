<?php

namespace App\Http\Controllers\Achats;

use App\Http\Controllers\Controller;
use App\Models\BonCommande;
use App\Models\BonReception;
use App\Models\BonReceptionLigne;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonReceptionController extends Controller
{
    public function index(Request $request)
    {
        $query = BonReception::with(['bonCommande', 'fournisseur', 'user'])->latest('date_reception');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_reception', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_reception', '<=', $request->date_fin);
        }

        $receptions = $query->paginate(15)->withQueryString();

        $stats = [
            'ce_mois'     => BonReception::whereMonth('date_reception', now()->month)
                ->whereYear('date_reception', now()->year)->count(),
            'en_attente'  => BonReception::where('statut', 'en_attente')->count(),
            'valides'     => BonReception::where('statut', 'valide')
                ->whereMonth('date_reception', now()->month)
                ->whereYear('date_reception', now()->year)->count(),
        ];

        return view('achats.receptions.index', compact('receptions', 'stats'));
    }

    public function create(Request $request)
    {
        $bonCommande = null;

        if ($request->filled('bon_commande_id')) {
            $bonCommande = BonCommande::with(['lignes.equipement', 'fournisseur'])
                ->whereIn('statut', ['confirmee', 'partiellement_recue'])
                ->findOrFail($request->bon_commande_id);
        }

        $commandesOuvertes = BonCommande::with('fournisseur')
            ->whereIn('statut', ['confirmee', 'partiellement_recue'])
            ->orderByDesc('date_commande')->get();

        return view('achats.receptions.create', compact('bonCommande', 'commandesOuvertes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bon_commande_id'        => 'required|exists:bons_commande,id',
            'date_reception'         => 'required|date',
            'transporteur'           => 'nullable|string|max:255',
            'numero_bl_fournisseur'  => 'nullable|string|max:100',
            'observations'           => 'nullable|string',
            'lignes'                 => 'required|array|min:1',
            'lignes.*.bon_commande_ligne_id' => 'required|exists:bon_commande_lignes,id',
            'lignes.*.quantite_recue'        => 'required|numeric|min:0',
            'lignes.*.quantite_conforme'     => 'required|numeric|min:0',
        ]);

        $bc = BonCommande::with('lignes')->findOrFail($request->bon_commande_id);
        abort_if(!in_array($bc->statut, ['confirmee', 'partiellement_recue']), 403, 'Ce BC ne peut plus recevoir de réceptions.');

        DB::transaction(function () use ($request, $bc) {
            $br = BonReception::create([
                'numero'                => BonReception::genererNumero(),
                'bon_commande_id'       => $bc->id,
                'fournisseur_id'        => $bc->fournisseur_id,
                'user_id'               => auth()->id(),
                'date_reception'        => $request->date_reception,
                'statut'                => 'en_attente',
                'transporteur'          => $request->transporteur,
                'numero_bl_fournisseur' => $request->numero_bl_fournisseur,
                'observations'          => $request->observations,
            ]);

            foreach ($request->lignes as $ligneData) {
                if (($ligneData['quantite_recue'] ?? 0) <= 0) continue;

                $bcLigne = $bc->lignes->find($ligneData['bon_commande_ligne_id']);
                if (!$bcLigne) continue;

                $qteConforme = min($ligneData['quantite_conforme'], $ligneData['quantite_recue']);
                $qteRejetee  = $ligneData['quantite_recue'] - $qteConforme;

                BonReceptionLigne::create([
                    'bon_reception_id'      => $br->id,
                    'bon_commande_ligne_id' => $bcLigne->id,
                    'equipement_id'         => $bcLigne->equipement_id,
                    'designation'           => $bcLigne->designation,
                    'quantite_recue'        => $ligneData['quantite_recue'],
                    'quantite_conforme'     => $qteConforme,
                    'quantite_rejetee'      => $qteRejetee,
                    'motif_rejet'           => $qteRejetee > 0 ? ($ligneData['motif_rejet'] ?? null) : null,
                ]);

                // Mise à jour quantité reçue sur la ligne BC
                $bcLigne->increment('quantite_recue', $ligneData['quantite_recue']);
            }

            // Mise à jour statut BC
            $bc->load('lignes');
            $bc->mettreAJourStatut();

            return $br;
        });

        return redirect()->route('achats.receptions.index')
            ->with('success', 'Bon de réception enregistré. Validez-le pour mettre à jour le stock.');
    }

    public function show(BonReception $bonReception)
    {
        $bonReception->load(['bonCommande.fournisseur', 'fournisseur', 'user', 'lignes.equipement', 'lignes.bonCommandeLigne']);
        return view('achats.receptions.show', compact('bonReception'));
    }

    public function valider(BonReception $bonReception)
    {
        abort_if($bonReception->statut !== 'en_attente', 403);

        DB::transaction(function () use ($bonReception) {
            $bonReception->load('lignes.equipement');

            // Créer les mouvements de stock pour chaque ligne conforme
            foreach ($bonReception->lignes as $ligne) {
                if ($ligne->quantite_conforme <= 0 || !$ligne->equipement_id) continue;

                $equipement     = $ligne->equipement;
                $quantiteAvant  = (int) $equipement->quantite;
                $quantiteApres  = $quantiteAvant + (int) $ligne->quantite_conforme;

                MouvementStock::create([
                    'equipement_id'   => $equipement->id,
                    'user_id'         => auth()->id(),
                    'type'            => 'entree',
                    'quantite'        => (int) $ligne->quantite_conforme,
                    'quantite_avant'  => $quantiteAvant,
                    'quantite_apres'  => $quantiteApres,
                    'motif'           => "Réception BC {$bonReception->bonCommande->numero} — {$bonReception->numero}",
                    'date_mouvement'  => $bonReception->date_reception,
                ]);

                $equipement->update(['quantite' => $quantiteApres]);
            }

            $bonReception->update(['statut' => 'valide']);
        });

        return back()->with('success', 'Réception validée et stock mis à jour.');
    }

    public function rejeter(BonReception $bonReception)
    {
        abort_if($bonReception->statut !== 'en_attente', 403);

        // Annuler les quantités reçues sur le BC
        foreach ($bonReception->lignes as $ligne) {
            $ligne->bonCommandeLigne->decrement('quantite_recue', $ligne->quantite_recue);
        }

        $bonReception->bonCommande->load('lignes');
        $bonReception->bonCommande->mettreAJourStatut();
        $bonReception->update(['statut' => 'rejete']);

        return back()->with('success', 'Bon de réception rejeté.');
    }
}
