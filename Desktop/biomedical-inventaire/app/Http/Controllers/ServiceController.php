<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('equipements')->orderBy('nom')->paginate(20);
        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'          => 'required|string|max:100|unique:services',
            'batiment'     => 'nullable|string|max:100',
            'etage'        => 'nullable|string|max:20',
            'responsable'  => 'nullable|string|max:100',
            'telephone'    => 'nullable|string|max:20',
        ]);

        Service::create($validated);
        return back()->with('success', 'Service créé.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'nom'         => "required|string|max:100|unique:services,nom,{$service->id}",
            'batiment'    => 'nullable|string|max:100',
            'etage'       => 'nullable|string|max:20',
            'responsable' => 'nullable|string|max:100',
            'telephone'   => 'nullable|string|max:20',
        ]);

        $service->update($validated);
        return back()->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service)
    {
        if ($service->equipements()->exists()) {
            return back()->withErrors(['service' => 'Impossible de supprimer : des équipements sont affectés à ce service.']);
        }

        $service->delete();
        return back()->with('success', 'Service supprimé.');
    }
}
