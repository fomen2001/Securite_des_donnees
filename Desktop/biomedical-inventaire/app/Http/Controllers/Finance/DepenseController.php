<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CategorieDepense;
use App\Models\Depense;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Depense::with('categorie', 'fournisseur')->latest('date_depense');

        if ($request->filled('recherche')) {
            $q = $request->recherche;
            $query->where(function ($s) use ($q) {
                $s->where('libelle', 'like', "%{$q}%")
                  ->orWhere('reference', 'like', "%{$q}%")
                  ->orWhere('beneficiaire', 'like', "%{$q}%");
            });
        }
        if ($request->filled('categorie_id')) {
            $query->where('categorie_depense_id', $request->categorie_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('mois')) {
            $query->whereMonth('date_depense', $request->mois);
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_depense', $request->annee);
        }

        $depenses   = $query->paginate(20)->withQueryString();
        $categories = CategorieDepense::orderBy('nom')->get();
        $annees     = range(now()->year, now()->year - 3);
        $totalFiltre = $query->sum('montant_ttc');

        return view('finance.depenses.index', compact('depenses', 'categories', 'annees', 'totalFiltre'));
    }

    public function create()
    {
        $categories  = CategorieDepense::orderBy('nom')->get();
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->get();
        $reference   = Depense::genererReference();
        return view('finance.depenses.create', compact('categories', 'fournisseurs', 'reference'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference'          => 'required|string|unique:depenses',
            'categorie_depense_id' => 'required|exists:categories_depenses,id',
            'libelle'            => 'required|string|max:255',
            'montant_ht'         => 'required|numeric|min:0',
            'tva'                => 'nullable|numeric|min:0|max:100',
            'date_depense'       => 'required|date',
            'mode_paiement'      => 'required|in:especes,virement,cheque,mobile_money,carte,autre',
            'beneficiaire'       => 'nullable|string|max:200',
            'fournisseur_id'     => 'nullable|exists:fournisseurs,id',
            'numero_piece'       => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
        ]);

        $tva         = (float) ($validated['tva'] ?? 0);
        $montant_ht  = (float) $validated['montant_ht'];
        $montant_ttc = $montant_ht * (1 + $tva / 100);

        Depense::create(array_merge($validated, [
            'montant_ttc' => round($montant_ttc, 2),
            'tva'         => $tva,
            'statut'      => 'en_attente',
            'created_by'  => auth()->id(),
        ]));

        return redirect()->route('finance.depenses.index')
            ->with('success', "Dépense \"{$validated['libelle']}\" enregistrée.");
    }

    public function show(Depense $depense)
    {
        $depense->load('categorie', 'fournisseur', 'approbateur', 'createdBy');
        return view('finance.depenses.show', compact('depense'));
    }

    public function edit(Depense $depense)
    {
        $categories   = CategorieDepense::orderBy('nom')->get();
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->get();
        return view('finance.depenses.edit', compact('depense', 'categories', 'fournisseurs'));
    }

    public function update(Request $request, Depense $depense)
    {
        $validated = $request->validate([
            'categorie_depense_id' => 'required|exists:categories_depenses,id',
            'libelle'              => 'required|string|max:255',
            'montant_ht'           => 'required|numeric|min:0',
            'tva'                  => 'nullable|numeric|min:0|max:100',
            'date_depense'         => 'required|date',
            'mode_paiement'        => 'required|in:especes,virement,cheque,mobile_money,carte,autre',
            'beneficiaire'         => 'nullable|string|max:200',
            'fournisseur_id'       => 'nullable|exists:fournisseurs,id',
            'numero_piece'         => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ]);

        $tva         = (float) ($validated['tva'] ?? 0);
        $montant_ttc = (float) $validated['montant_ht'] * (1 + $tva / 100);

        $depense->update(array_merge($validated, [
            'montant_ttc' => round($montant_ttc, 2),
            'tva'         => $tva,
        ]));

        return redirect()->route('finance.depenses.show', $depense)
            ->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Depense $depense)
    {
        $depense->delete();
        return redirect()->route('finance.depenses.index')
            ->with('success', 'Dépense supprimée.');
    }

    public function approuver(Depense $depense)
    {
        abort_if($depense->statut !== 'en_attente', 403);
        $depense->update([
            'statut'           => 'approuvee',
            'approuve_par'     => auth()->id(),
            'date_approbation' => now(),
        ]);
        return back()->with('success', 'Dépense approuvée.');
    }

    public function payer(Request $request, Depense $depense)
    {
        abort_if($depense->statut === 'payee', 403);
        $request->validate(['mode_paiement' => 'required|in:especes,virement,cheque,mobile_money,carte,autre']);
        $depense->update(['statut' => 'payee', 'mode_paiement' => $request->mode_paiement]);
        return back()->with('success', 'Dépense marquée comme payée.');
    }

    public function rejeter(Depense $depense)
    {
        abort_if($depense->statut !== 'en_attente', 403);
        $depense->update(['statut' => 'rejetee', 'approuve_par' => auth()->id()]);
        return back()->with('success', 'Dépense rejetée.');
    }
}
