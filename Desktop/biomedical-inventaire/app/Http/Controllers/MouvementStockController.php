<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\MouvementStock;
use App\Models\Service;
use Illuminate\Http\Request;

class MouvementStockController extends Controller
{
    public function index(Request $request)
    {
        $query = MouvementStock::with(['equipement', 'user', 'serviceSource', 'serviceDestination'])
            ->latest('date_mouvement');

        if ($request->filled('equipement_id')) {
            $query->where('equipement_id', $request->equipement_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_mouvement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_mouvement', '<=', $request->date_fin);
        }

        $mouvements = $query->paginate(20)->withQueryString();

        return view('mouvements.index', compact('mouvements'));
    }

    public function create(Request $request)
    {
        $equipement = $request->filled('equipement_id')
            ? Equipement::findOrFail($request->equipement_id)
            : null;

        $equipements = Equipement::whereNotIn('etat', ['reformé'])
            ->orderBy('designation')
            ->get(['id', 'designation', 'code_inventaire', 'quantite', 'service_id']);

        $services = Service::orderBy('nom')->pluck('nom', 'id');

        return view('mouvements.create', compact('equipement', 'equipements', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipement_id'          => 'required|exists:equipements,id',
            'type'                   => 'required|in:entree,sortie,transfert,retour,ajustement,reforme',
            'quantite'               => 'required|integer|min:1',
            'service_source_id'      => 'nullable|exists:services,id',
            'service_destination_id' => 'nullable|exists:services,id',
            'reference_document'     => 'nullable|string|max:100',
            'motif'                  => 'nullable|string',
        ]);

        $equipement = Equipement::findOrFail($validated['equipement_id']);
        $quantiteAvant = $equipement->quantite;

        // Calcul de la nouvelle quantité
        $nouvelleQte = match ($validated['type']) {
            'entree', 'retour', 'ajustement' => $quantiteAvant + $validated['quantite'],
            'sortie', 'reforme'              => $quantiteAvant - $validated['quantite'],
            'transfert'                      => $quantiteAvant, // quantité inchangée
            default                          => $quantiteAvant,
        };

        if ($nouvelleQte < 0) {
            return back()->withErrors(['quantite' => 'La quantité en stock ne peut pas être négative.'])->withInput();
        }

        MouvementStock::create(array_merge($validated, [
            'quantite_avant' => $quantiteAvant,
            'quantite_apres' => $nouvelleQte,
            'user_id'        => auth()->id(),
            'date_mouvement' => now(),
        ]));

        // Mise à jour stock et service si transfert
        $updateData = ['quantite' => $nouvelleQte];

        if ($validated['type'] === 'transfert' && $validated['service_destination_id']) {
            $updateData['service_id'] = $validated['service_destination_id'];
        }

        if ($validated['type'] === 'reforme') {
            $updateData['etat'] = 'reformé';
        }

        $equipement->update($updateData);

        return redirect()->route('equipements.show', $equipement)
            ->with('success', 'Mouvement de stock enregistré.');
    }
}
