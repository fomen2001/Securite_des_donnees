<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount('ventes');

        if ($request->filled('recherche')) {
            $q = $request->recherche;
            $query->where(function ($s) use ($q) {
                $s->where('nom', 'like', "%{$q}%")
                  ->orWhere('code_client', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('telephone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $clients = $query->orderBy('nom')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $code = Client::genererCode();
        return view('clients.create', compact('code'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_client'          => 'required|string|unique:clients',
            'nom'                  => 'required|string|max:200',
            'type'                 => 'required|in:hopital,clinique,cabinet,laboratoire,particulier,autre',
            'contact_nom'          => 'nullable|string|max:100',
            'telephone'            => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:150',
            'adresse'              => 'nullable|string',
            'ville'                => 'nullable|string|max:100',
            'pays'                 => 'nullable|string|max:80',
            'numero_contribuable'  => 'nullable|string|max:50',
            'statut'               => 'required|in:actif,inactif',
            'notes'                => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', "Client \"{$client->nom}\" créé.");
    }

    public function show(Client $client)
    {
        $client->load(['ventes' => fn ($q) => $q->latest()->limit(10)]);

        $stats = [
            'total_ventes'   => $client->ventes()->count(),
            'chiffre_affaires' => $client->ventes()->whereIn('statut', ['payee', 'livree', 'facturee'])->sum('total_ttc'),
            'impayees'       => $client->ventes()->whereIn('statut', ['facturee', 'livree'])->whereColumn('montant_paye', '<', 'total_ttc')->count(),
        ];

        return view('clients.show', compact('client', 'stats'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'code_client'          => "required|string|unique:clients,code_client,{$client->id}",
            'nom'                  => 'required|string|max:200',
            'type'                 => 'required|in:hopital,clinique,cabinet,laboratoire,particulier,autre',
            'contact_nom'          => 'nullable|string|max:100',
            'telephone'            => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:150',
            'adresse'              => 'nullable|string',
            'ville'                => 'nullable|string|max:100',
            'pays'                 => 'nullable|string|max:80',
            'numero_contribuable'  => 'nullable|string|max:50',
            'statut'               => 'required|in:actif,inactif',
            'notes'                => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'Client archivé.');
    }
}
