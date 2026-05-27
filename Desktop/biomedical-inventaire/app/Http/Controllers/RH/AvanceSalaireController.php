<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\AvanceSalaire;
use App\Models\Employe;
use Illuminate\Http\Request;

class AvanceSalaireController extends Controller
{
    public function index(Request $request)
    {
        $query = AvanceSalaire::with('employe')->latest();

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('annee')) {
            $query->where('annee_deduction', $request->annee);
        }

        $avances   = $query->paginate(20)->withQueryString();
        $employes  = Employe::actifs()->orderBy('nom')->get();
        $annees    = range(now()->year, now()->year - 3);

        $stats = [
            'en_attente'  => AvanceSalaire::where('statut', 'en_attente')->count(),
            'total_approuvees' => AvanceSalaire::where('statut', 'approuvee')->sum('montant'),
        ];

        return view('rh.avances.index', compact('avances', 'employes', 'annees', 'stats'));
    }

    public function create(Request $request)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        $employe  = $request->filled('employe_id') ? Employe::find($request->employe_id) : null;
        return view('rh.avances.create', compact('employes', 'employe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'      => 'required|exists:employes,id',
            'montant'         => 'required|numeric|min:1000',
            'date_avance'     => 'required|date',
            'mois_deduction'  => 'required|integer|between:1,12',
            'annee_deduction' => 'required|integer|min:2020',
            'motif'           => 'nullable|string|max:500',
        ]);

        $employe = Employe::findOrFail($request->employe_id);

        // Vérifier qu'il n'y a pas déjà une avance non remboursée pour ce mois
        $existe = AvanceSalaire::where('employe_id', $employe->id)
            ->where('mois_deduction', $request->mois_deduction)
            ->where('annee_deduction', $request->annee_deduction)
            ->whereIn('statut', ['en_attente', 'approuvee'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['mois_deduction' => "Une avance est déjà prévue pour {$employe->nom_complet} en {$request->mois_deduction}/{$request->annee_deduction}."])->withInput();
        }

        // Vérifier que l'avance ne dépasse pas 50% du salaire
        $limite = $employe->salaire_base * 0.5;
        if ($request->montant > $limite) {
            return back()->withErrors(['montant' => "L'avance ne peut dépasser 50 % du salaire de base (" . number_format($limite, 0, ',', ' ') . " FCFA)."])->withInput();
        }

        AvanceSalaire::create($request->only([
            'employe_id', 'montant', 'date_avance',
            'mois_deduction', 'annee_deduction', 'motif',
        ]));

        return redirect()->route('rh.avances.index')
            ->with('success', "Demande d'avance enregistrée pour {$employe->nom_complet}.");
    }

    public function approuver(AvanceSalaire $avance)
    {
        abort_if($avance->statut !== 'en_attente', 403);
        $avance->update([
            'statut'           => 'approuvee',
            'approuve_par'     => auth()->id(),
            'date_approbation' => now(),
        ]);
        return back()->with('success', 'Avance approuvée. Elle sera déduite du prochain bulletin.');
    }

    public function annuler(AvanceSalaire $avance)
    {
        abort_if(!in_array($avance->statut, ['en_attente', 'approuvee']), 403);
        $avance->update(['statut' => 'annulee']);
        return back()->with('success', 'Avance annulée.');
    }
}
