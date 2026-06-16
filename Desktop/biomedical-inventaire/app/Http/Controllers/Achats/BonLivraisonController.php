<?php

namespace App\Http\Controllers\Achats;

use App\Http\Controllers\Controller;
use App\Models\BonLivraison;
use App\Models\BonLivraisonLigne;
use App\Models\Client;
use App\Models\Equipement;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $query = BonLivraison::with(['client', 'vente', 'user'])->latest('date_livraison');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_livraison', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_livraison', '<=', $request->date_fin);
        }

        $livraisons = $query->paginate(15)->withQueryString();

        $stats = [
            'ce_mois'   => BonLivraison::whereMonth('date_livraison', now()->month)
                ->whereYear('date_livraison', now()->year)->count(),
            'prepares'  => BonLivraison::where('statut', 'prepare')->count(),
            'expedies'  => BonLivraison::where('statut', 'expedie')->count(),
            'livres'    => BonLivraison::where('statut', 'livre')
                ->whereMonth('date_livraison', now()->month)
                ->whereYear('date_livraison', now()->year)->count(),
        ];

        $clients = Client::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');

        return view('achats.livraisons.index', compact('livraisons', 'stats', 'clients'));
    }

    public function create(Request $request)
    {
        $vente = null;

        if ($request->filled('vente_id')) {
            $vente = Vente::with(['client', 'lignes.equipement'])
                ->whereIn('statut', ['confirmee', 'facturee', 'livree'])
                ->findOrFail($request->vente_id);
        }

        $clients    = Client::where('statut', 'actif')->orderBy('nom')->get(['id', 'nom', 'adresse', 'telephone']);
        $equipements = Equipement::where('quantite', '>', 0)->orderBy('nom')->get(['id', 'nom', 'reference', 'quantite']);
        $ventes     = Vente::with('client')
            ->whereIn('statut', ['confirmee', 'facturee'])
            ->orderByDesc('date_vente')->get();

        return view('achats.livraisons.create', compact('vente', 'clients', 'equipements', 'ventes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'           => 'required|exists:clients,id',
            'date_livraison'      => 'required|date',
            'adresse_livraison'   => 'nullable|string|max:500',
            'transporteur'        => 'nullable|string|max:255',
            'contact_reception'   => 'nullable|string|max:255',
            'observations'        => 'nullable|string',
            'lignes'              => 'required|array|min:1',
            'lignes.*.designation'=> 'required|string|max:255',
            'lignes.*.quantite'   => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $bl = BonLivraison::create([
                'numero'             => BonLivraison::genererNumero(),
                'vente_id'           => $request->vente_id ?: null,
                'client_id'          => $request->client_id,
                'user_id'            => auth()->id(),
                'date_livraison'     => $request->date_livraison,
                'statut'             => 'prepare',
                'adresse_livraison'  => $request->adresse_livraison,
                'transporteur'       => $request->transporteur,
                'contact_reception'  => $request->contact_reception,
                'observations'       => $request->observations,
            ]);

            foreach ($request->lignes as $ligne) {
                BonLivraisonLigne::create([
                    'bon_livraison_id' => $bl->id,
                    'equipement_id'    => $ligne['equipement_id'] ?? null,
                    'designation'      => $ligne['designation'],
                    'reference'        => $ligne['reference'] ?? null,
                    'quantite'         => $ligne['quantite'],
                    'unite'            => $ligne['unite'] ?? 'unité',
                    'observations'     => $ligne['observations'] ?? null,
                ]);
            }
        });

        return redirect()->route('achats.livraisons.index')
            ->with('success', 'Bon de livraison créé avec succès.');
    }

    public function show(BonLivraison $bonLivraison)
    {
        $bonLivraison->load(['client', 'vente', 'user', 'lignes.equipement']);
        return view('achats.livraisons.show', compact('bonLivraison'));
    }

    public function expedier(BonLivraison $bonLivraison)
    {
        abort_if($bonLivraison->statut !== 'prepare', 403);
        $bonLivraison->update(['statut' => 'expedie']);
        return back()->with('success', 'Bon de livraison marqué comme expédié.');
    }

    public function livrer(BonLivraison $bonLivraison)
    {
        abort_if($bonLivraison->statut !== 'expedie', 403);
        $bonLivraison->update(['statut' => 'livre']);

        // Si lié à une vente, la marquer comme livrée
        if ($bonLivraison->vente_id) {
            $bonLivraison->vente->update([
                'statut'                => 'livree',
                'date_livraison_reelle' => $bonLivraison->date_livraison,
            ]);
        }

        return back()->with('success', 'Livraison confirmée.');
    }

    public function annuler(BonLivraison $bonLivraison)
    {
        abort_if(!in_array($bonLivraison->statut, ['prepare', 'expedie']), 403);
        $bonLivraison->update(['statut' => 'annule']);
        return back()->with('success', 'Bon de livraison annulé.');
    }
}
