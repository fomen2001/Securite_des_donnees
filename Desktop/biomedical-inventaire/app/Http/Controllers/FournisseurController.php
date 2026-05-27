<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::withCount('equipements')->orderBy('nom')->paginate(15);
        return view('fournisseurs.index', compact('fournisseurs'));
    }

    public function create()
    {
        return view('fournisseurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'contact_nom' => 'nullable|string|max:100',
            'telephone'   => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:100',
            'adresse'     => 'nullable|string',
            'pays'        => 'nullable|string|max:80',
            'site_web'    => 'nullable|url|max:200',
            'statut'      => 'required|in:actif,inactif',
        ]);

        $fournisseur = Fournisseur::create($validated);

        return redirect()->route('fournisseurs.index')
            ->with('success', "Fournisseur \"{$fournisseur->nom}\" créé.");
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->load(['equipements.categorie', 'equipements.service', 'maintenances.equipement']);
        return view('fournisseurs.show', compact('fournisseur'));
    }

    public function edit(Fournisseur $fournisseur)
    {
        return view('fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:150',
            'contact_nom' => 'nullable|string|max:100',
            'telephone'   => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:100',
            'adresse'     => 'nullable|string',
            'pays'        => 'nullable|string|max:80',
            'site_web'    => 'nullable|url|max:200',
            'statut'      => 'required|in:actif,inactif',
        ]);

        $fournisseur->update($validated);

        return redirect()->route('fournisseurs.show', $fournisseur)
            ->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return redirect()->route('fournisseurs.index')
            ->with('success', 'Fournisseur archivé.');
    }
}
