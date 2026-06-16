<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Visiteur;
use Illuminate\Http\Request;

class VisiteurController extends Controller
{
    public function index(Request $request)
    {
        $query = Visiteur::with('employe', 'user')->latest('date_entree');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($b) => $b->where('nom', 'like', "%$q%")
                ->orWhere('prenom', 'like', "%$q%")
                ->orWhere('entreprise', 'like', "%$q%")
                ->orWhere('personne_visitee', 'like', "%$q%"));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('date')) {
            $query->whereDate('date_entree', $request->date);
        }

        $visiteurs   = $query->paginate(20)->withQueryString();
        $stats       = [
            'aujourd_hui'  => Visiteur::whereDate('date_entree', today())->count(),
            'en_attente'   => Visiteur::where('statut', 'en_attente')->count(),
            'en_cours'     => Visiteur::where('statut', 'recu')->count(),
            'ce_mois'      => Visiteur::whereMonth('date_entree', now()->month)->whereYear('date_entree', now()->year)->count(),
        ];

        return view('secretariat.visiteurs.index', compact('visiteurs', 'stats'));
    }

    public function create()
    {
        $employes = Employe::orderBy('nom')->get();
        return view('secretariat.visiteurs.create', compact('employes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'              => 'required|string|max:100',
            'prenom'           => 'nullable|string|max:100',
            'entreprise'       => 'nullable|string|max:150',
            'telephone'        => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:150',
            'objet_visite'     => 'required|string|max:255',
            'personne_visitee' => 'required|string|max:150',
            'employe_id'       => 'nullable|exists:employes,id',
            'date_entree'      => 'required|date',
            'badge_numero'     => 'nullable|string|max:50',
            'observations'     => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();
        $data['statut']  = 'en_attente';

        Visiteur::create($data);

        return redirect()->route('secretariat.visiteurs.index')
            ->with('success', 'Visiteur enregistré.');
    }

    public function show(Visiteur $visiteur)
    {
        return view('secretariat.visiteurs.show', compact('visiteur'));
    }

    public function recevoir(Visiteur $visiteur)
    {
        $visiteur->update(['statut' => 'recu']);
        return back()->with('success', 'Visiteur marqué comme reçu.');
    }

    public function sortir(Visiteur $visiteur)
    {
        $visiteur->update([
            'statut'      => 'sorti',
            'date_sortie' => now(),
        ]);
        return back()->with('success', 'Départ enregistré.');
    }

    public function annuler(Visiteur $visiteur)
    {
        $visiteur->update(['statut' => 'annule']);
        return back()->with('success', 'Visite annulée.');
    }
}
