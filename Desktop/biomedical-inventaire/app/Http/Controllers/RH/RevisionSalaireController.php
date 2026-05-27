<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\RevisionSalaire;
use Illuminate\Http\Request;

class RevisionSalaireController extends Controller
{
    public function index(Request $request)
    {
        $query = RevisionSalaire::with('employe', 'approbateur')->latest('date_effet');

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_effet', $request->annee);
        }

        $revisions = $query->paginate(20)->withQueryString();
        $employes  = Employe::orderBy('nom')->get();
        $annees    = range(now()->year, now()->year - 4);

        return view('rh.revisions.index', compact('revisions', 'employes', 'annees'));
    }

    public function create(Request $request)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        $employe  = $request->filled('employe_id') ? Employe::find($request->employe_id) : null;
        return view('rh.revisions.create', compact('employes', 'employe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'     => 'required|exists:employes,id',
            'nouveau_salaire'=> 'required|numeric|min:41875',
            'date_effet'     => 'required|date',
            'motif'          => 'required|in:augmentation_merite,promotion,reclassement,anciennete,revision_annuelle,autre',
            'commentaire'    => 'nullable|string|max:500',
        ]);

        $employe = Employe::findOrFail($request->employe_id);

        if ($request->nouveau_salaire == $employe->salaire_base) {
            return back()->withErrors(['nouveau_salaire' => 'Le nouveau salaire est identique au salaire actuel.'])->withInput();
        }

        // Enregistrer la révision
        RevisionSalaire::create([
            'employe_id'     => $employe->id,
            'ancien_salaire' => $employe->salaire_base,
            'nouveau_salaire'=> $request->nouveau_salaire,
            'date_effet'     => $request->date_effet,
            'motif'          => $request->motif,
            'commentaire'    => $request->commentaire,
            'approuve_par'   => auth()->id(),
        ]);

        // Mettre à jour le salaire de l'employé si la date d'effet est passée/aujourd'hui
        if ($request->date_effet <= now()->toDateString()) {
            $employe->update(['salaire_base' => $request->nouveau_salaire]);
        }

        $variation = (($request->nouveau_salaire - $employe->salaire_base) / $employe->salaire_base) * 100;
        $sens      = $variation >= 0 ? 'augmenté' : 'réduit';

        return redirect()->route('rh.revisions.index')
            ->with('success', "Salaire de {$employe->nom_complet} {$sens} de " . abs(round($variation, 1)) . " %.");
    }
}
