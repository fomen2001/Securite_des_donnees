<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount('equipements')->orderBy('nom')->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100|unique:categories',
            'description' => 'nullable|string',
            'couleur'     => 'nullable|string|size:7',
        ]);

        Categorie::create($validated);

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $validated = $request->validate([
            'nom'         => "required|string|max:100|unique:categories,nom,{$categorie->id}",
            'description' => 'nullable|string',
            'couleur'     => 'nullable|string|size:7',
        ]);

        $categorie->update($validated);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->equipements()->exists()) {
            return back()->withErrors(['categorie' => 'Impossible de supprimer : des équipements utilisent cette catégorie.']);
        }

        $categorie->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }
}
