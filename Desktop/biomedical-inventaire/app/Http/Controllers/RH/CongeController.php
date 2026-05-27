<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Conge;
use App\Models\Employe;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index(Request $request)
    {
        $query = Conge::with('employe')->latest();

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type_conge')) {
            $query->where('type_conge', $request->type_conge);
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_debut', $request->annee);
        }

        $conges   = $query->paginate(20)->withQueryString();
        $employes = Employe::actifs()->orderBy('nom')->get();
        $annees   = range(now()->year, now()->year - 3);

        $stats = [
            'en_attente' => Conge::where('statut', 'en_attente')->count(),
            'approuves'  => Conge::where('statut', 'approuve')->whereYear('date_debut', now()->year)->count(),
        ];

        return view('rh.conges.index', compact('conges', 'employes', 'annees', 'stats'));
    }

    public function create(Request $request)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        $employe  = $request->filled('employe_id') ? Employe::find($request->employe_id) : null;
        return view('rh.conges.create', compact('employes', 'employe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'  => 'required|exists:employes,id',
            'type_conge'  => 'required|in:annuel,maladie,maternite,paternite,sans_solde,deuil,autre',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'nullable|string|max:500',
        ]);

        $employe     = Employe::findOrFail($request->employe_id);
        $debut       = \Carbon\Carbon::parse($request->date_debut);
        $fin         = \Carbon\Carbon::parse($request->date_fin);
        $jours       = $this->calculerJoursOuvrables($debut, $fin);

        // Vérifier solde pour congé annuel
        if ($request->type_conge === 'annuel' && $employe->solde_conge < $jours) {
            return back()->withErrors(['date_fin' => "Solde insuffisant : {$employe->solde_conge} jour(s) disponible(s), {$jours} demandé(s)."])->withInput();
        }

        Conge::create([
            'employe_id'   => $employe->id,
            'type_conge'   => $request->type_conge,
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
            'nombre_jours' => $jours,
            'motif'        => $request->motif,
            'statut'       => 'en_attente',
            'solde_avant'  => $employe->solde_conge,
            'solde_apres'  => $employe->solde_conge, // mis à jour à l'approbation
        ]);

        return redirect()->route('rh.conges.index')
            ->with('success', "Demande de congé soumise pour {$employe->nom_complet} ({$jours} jour(s)).");
    }

    public function show(Conge $conge)
    {
        $conge->load('employe.service', 'approbateur');
        return view('rh.conges.show', compact('conge'));
    }

    public function approuver(Conge $conge)
    {
        abort_if($conge->statut !== 'en_attente', 403, 'Ce congé ne peut plus être approuvé.');

        $employe     = $conge->employe;
        $solde_avant = $employe->solde_conge;
        $solde_apres = $solde_avant;

        if ($conge->type_conge === 'annuel') {
            if ($employe->solde_conge < $conge->nombre_jours) {
                return back()->withErrors(['solde' => 'Solde de congé insuffisant.']);
            }
            $solde_apres = $solde_avant - $conge->nombre_jours;
            $employe->decrement('solde_conge', $conge->nombre_jours);
            $employe->update(['statut' => 'conge']);
        }

        $conge->update([
            'statut'          => 'approuve',
            'approuve_par'    => auth()->id(),
            'date_approbation'=> now(),
            'solde_avant'     => $solde_avant,
            'solde_apres'     => $solde_apres,
        ]);

        return back()->with('success', "Congé approuvé pour {$employe->nom_complet}.");
    }

    public function refuser(Request $request, Conge $conge)
    {
        abort_if($conge->statut !== 'en_attente', 403);
        $request->validate(['motif_refus' => 'required|string|max:500']);

        $conge->update([
            'statut'       => 'refuse',
            'approuve_par' => auth()->id(),
            'date_approbation' => now(),
            'motif_refus'  => $request->motif_refus,
        ]);

        return back()->with('success', 'Demande de congé refusée.');
    }

    public function annuler(Conge $conge)
    {
        abort_if(!in_array($conge->statut, ['en_attente', 'approuve']), 403);

        // Restituer le solde si congé annuel approuvé
        if ($conge->statut === 'approuve' && $conge->type_conge === 'annuel') {
            $conge->employe->increment('solde_conge', $conge->nombre_jours);
            $conge->employe->update(['statut' => 'actif']);
        }

        $conge->update(['statut' => 'annule']);
        return back()->with('success', 'Congé annulé.');
    }

    // ── Calcul jours ouvrables (lundi–samedi au Cameroun) ─────────

    private function calculerJoursOuvrables(\Carbon\Carbon $debut, \Carbon\Carbon $fin): int
    {
        $jours = 0;
        $current = $debut->copy();
        while ($current->lte($fin)) {
            if ($current->dayOfWeek !== \Carbon\Carbon::SUNDAY) {
                $jours++;
            }
            $current->addDay();
        }
        return $jours;
    }
}
