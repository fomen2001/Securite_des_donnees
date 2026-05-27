<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Fournisseur;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::with(['equipement', 'user', 'fournisseur'])->latest('date_planifiee');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('equipement_id')) {
            $query->where('equipement_id', $request->equipement_id);
        }

        $maintenances = $query->paginate(15)->withQueryString();

        // Stats rapides
        $stats = [
            'planifiees' => Maintenance::where('statut', 'planifiee')->count(),
            'en_cours'   => Maintenance::where('statut', 'en_cours')->count(),
            'terminees'  => Maintenance::where('statut', 'terminee')->whereYear('date_fin', now()->year)->count(),
        ];

        return view('maintenances.index', compact('maintenances', 'stats'));
    }

    public function create(Request $request)
    {
        $equipement = $request->filled('equipement_id')
            ? Equipement::findOrFail($request->equipement_id)
            : null;

        $equipements  = Equipement::whereNotIn('etat', ['reformé'])->orderBy('designation')->get(['id', 'designation', 'code_inventaire']);
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');

        return view('maintenances.create', compact('equipement', 'equipements', 'fournisseurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipement_id'        => 'required|exists:equipements,id',
            'type'                 => 'required|in:preventive,corrective,calibration,verification',
            'statut'               => 'required|in:planifiee,en_cours,terminee,annulee',
            'date_planifiee'       => 'required|date',
            'date_debut'           => 'nullable|date',
            'date_fin'             => 'nullable|date|after_or_equal:date_debut',
            'technicien'           => 'nullable|string|max:150',
            'fournisseur_id'       => 'nullable|exists:fournisseurs,id',
            'description_travaux'  => 'nullable|string',
            'observations'         => 'nullable|string',
            'cout'                 => 'nullable|numeric|min:0',
            'rapport'              => 'nullable|file|mimes:pdf|max:5120',
            'equipement_operationnel' => 'boolean',
            'prochaine_maintenance'=> 'nullable|date',
        ]);

        if ($request->hasFile('rapport')) {
            $validated['rapport_path'] = $request->file('rapport')->store('rapports-maintenance', 'public');
        }

        $validated['user_id'] = auth()->id();
        $maintenance = Maintenance::create($validated);

        // Mettre à jour l'état et la prochaine maintenance de l'équipement
        $equipement = Equipement::find($validated['equipement_id']);
        $updateEquipement = [];

        if ($validated['statut'] === 'en_cours') {
            $updateEquipement['etat'] = 'en_maintenance';
        } elseif ($validated['statut'] === 'terminee') {
            $updateEquipement['etat'] = $validated['equipement_operationnel'] ? 'operationnel' : 'hors_service';
            if (!empty($validated['prochaine_maintenance'])) {
                $updateEquipement['prochaine_maintenance'] = $validated['prochaine_maintenance'];
            }
        }

        if ($updateEquipement) {
            $equipement->update($updateEquipement);
        }

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Maintenance enregistrée.');
    }

    public function show(Maintenance $maintenance)
    {
        $maintenance->load(['equipement.categorie', 'equipement.service', 'user', 'fournisseur']);

        return view('maintenances.show', compact('maintenance'));
    }

    public function edit(Maintenance $maintenance)
    {
        $equipements  = Equipement::whereNotIn('etat', ['reformé'])->orderBy('designation')->get(['id', 'designation', 'code_inventaire']);
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');

        return view('maintenances.edit', compact('maintenance', 'equipements', 'fournisseurs'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'equipement_id'        => 'required|exists:equipements,id',
            'type'                 => 'required|in:preventive,corrective,calibration,verification',
            'statut'               => 'required|in:planifiee,en_cours,terminee,annulee',
            'date_planifiee'       => 'required|date',
            'date_debut'           => 'nullable|date',
            'date_fin'             => 'nullable|date|after_or_equal:date_debut',
            'technicien'           => 'nullable|string|max:150',
            'fournisseur_id'       => 'nullable|exists:fournisseurs,id',
            'description_travaux'  => 'nullable|string',
            'observations'         => 'nullable|string',
            'cout'                 => 'nullable|numeric|min:0',
            'rapport'              => 'nullable|file|mimes:pdf|max:5120',
            'equipement_operationnel' => 'boolean',
            'prochaine_maintenance'=> 'nullable|date',
        ]);

        if ($request->hasFile('rapport')) {
            if ($maintenance->rapport_path) {
                Storage::disk('public')->delete($maintenance->rapport_path);
            }
            $validated['rapport_path'] = $request->file('rapport')->store('rapports-maintenance', 'public');
        }

        $maintenance->update($validated);

        // Sync état équipement
        $equipement = Equipement::find($validated['equipement_id']);
        if ($validated['statut'] === 'terminee') {
            $equipement->update([
                'etat'                 => $validated['equipement_operationnel'] ? 'operationnel' : 'hors_service',
                'prochaine_maintenance'=> $validated['prochaine_maintenance'] ?? $equipement->prochaine_maintenance,
            ]);
        }

        return redirect()->route('maintenances.show', $maintenance)
            ->with('success', 'Maintenance mise à jour.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()->route('maintenances.index')
            ->with('success', 'Maintenance supprimée.');
    }
}
