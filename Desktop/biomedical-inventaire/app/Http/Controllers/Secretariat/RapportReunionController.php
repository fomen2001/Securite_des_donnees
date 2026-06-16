<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\RapportReunion;
use App\Models\RapportReunionParticipant;
use Illuminate\Http\Request;

class RapportReunionController extends Controller
{
    public function index(Request $request)
    {
        $query = RapportReunion::with('user')->withCount('participants')->latest('date_reunion');

        if ($request->filled('q')) {
            $query->where(fn($b) => $b->where('titre', 'like', '%' . $request->q . '%')
                ->orWhere('reference', 'like', '%' . $request->q . '%')
                ->orWhere('lieu', 'like', '%' . $request->q . '%'));
        }
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('statut')) $query->where('statut', $request->statut);

        $rapports = $query->paginate(20)->withQueryString();
        return view('secretariat.reunions.index', compact('rapports'));
    }

    public function create()
    {
        return view('secretariat.reunions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'            => 'required|string|max:255',
            'date_reunion'     => 'required|date',
            'lieu'             => 'nullable|string|max:200',
            'type'             => 'required|in:interne,client,fournisseur,partenaire,autre',
            'ordre_du_jour'    => 'nullable|string',
            'compte_rendu'     => 'required|string',
            'decisions'        => 'nullable|string',
            'actions_a_suivre' => 'nullable|string',
            'statut'           => 'required|in:brouillon,finalise',
            'participants'     => 'nullable|array',
            'participants.*.nom'       => 'required_with:participants|string|max:100',
            'participants.*.fonction'  => 'nullable|string|max:100',
            'participants.*.entreprise'=> 'nullable|string|max:150',
            'participants.*.present'   => 'nullable',
        ]);

        $rapport = RapportReunion::create([
            'reference'        => RapportReunion::genererReference(),
            'titre'            => $request->titre,
            'date_reunion'     => $request->date_reunion,
            'lieu'             => $request->lieu,
            'type'             => $request->type,
            'ordre_du_jour'    => $request->ordre_du_jour,
            'compte_rendu'     => $request->compte_rendu,
            'decisions'        => $request->decisions,
            'actions_a_suivre' => $request->actions_a_suivre,
            'statut'           => $request->statut,
            'user_id'          => auth()->id(),
        ]);

        foreach ($request->participants ?? [] as $p) {
            if (!empty($p['nom'])) {
                RapportReunionParticipant::create([
                    'rapport_reunion_id' => $rapport->id,
                    'nom'        => $p['nom'],
                    'fonction'   => $p['fonction'] ?? null,
                    'entreprise' => $p['entreprise'] ?? null,
                    'present'    => isset($p['present']) ? true : false,
                ]);
            }
        }

        return redirect()->route('secretariat.reunions.show', $rapport)
            ->with('success', 'Rapport de réunion créé.');
    }

    public function show(RapportReunion $reunion)
    {
        $reunion->load('participants', 'user');
        return view('secretariat.reunions.show', compact('reunion'));
    }

    public function edit(RapportReunion $reunion)
    {
        $reunion->load('participants');
        return view('secretariat.reunions.edit', compact('reunion'));
    }

    public function update(Request $request, RapportReunion $reunion)
    {
        $request->validate([
            'titre'            => 'required|string|max:255',
            'date_reunion'     => 'required|date',
            'lieu'             => 'nullable|string|max:200',
            'type'             => 'required|in:interne,client,fournisseur,partenaire,autre',
            'ordre_du_jour'    => 'nullable|string',
            'compte_rendu'     => 'required|string',
            'decisions'        => 'nullable|string',
            'actions_a_suivre' => 'nullable|string',
            'statut'           => 'required|in:brouillon,finalise',
        ]);

        $reunion->update($request->only([
            'titre', 'date_reunion', 'lieu', 'type',
            'ordre_du_jour', 'compte_rendu', 'decisions', 'actions_a_suivre', 'statut',
        ]));

        // Remplacer les participants
        $reunion->participants()->delete();
        foreach ($request->participants ?? [] as $p) {
            if (!empty($p['nom'])) {
                RapportReunionParticipant::create([
                    'rapport_reunion_id' => $reunion->id,
                    'nom'        => $p['nom'],
                    'fonction'   => $p['fonction'] ?? null,
                    'entreprise' => $p['entreprise'] ?? null,
                    'present'    => isset($p['present']) ? true : false,
                ]);
            }
        }

        return redirect()->route('secretariat.reunions.show', $reunion)
            ->with('success', 'Rapport mis à jour.');
    }

    public function destroy(RapportReunion $reunion)
    {
        $reunion->delete();
        return redirect()->route('secretariat.reunions.index')->with('success', 'Rapport supprimé.');
    }

    public function imprimer(RapportReunion $reunion)
    {
        $reunion->load('participants', 'user');
        return view('secretariat.reunions.imprimer', compact('reunion'));
    }
}
