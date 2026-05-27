<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Service;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class EmployeController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index(Request $request)
    {
        $query = Employe::with('service');

        if ($request->filled('recherche')) {
            $q = $request->recherche;
            $query->where(function ($s) use ($q) {
                $s->where('nom', 'like', "%{$q}%")
                  ->orWhere('prenom', 'like', "%{$q}%")
                  ->orWhere('matricule', 'like', "%{$q}%")
                  ->orWhere('poste', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type_contrat')) {
            $query->where('type_contrat', $request->type_contrat);
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        $employes = $query->orderBy('nom')->paginate(15)->withQueryString();
        $services = Service::orderBy('nom')->get();

        return view('rh.employes.index', compact('employes', 'services'));
    }

    public function create()
    {
        $matricule = Employe::genererMatricule();
        $services  = Service::orderBy('nom')->get();
        return view('rh.employes.create', compact('matricule', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Employe::create($validated);

        return redirect()->route('rh.employes.index')
            ->with('success', "Employé {$validated['prenom']} {$validated['nom']} enregistré avec succès.");
    }

    public function show(Employe $employe)
    {
        $employe->load(['service', 'bulletinsPaie' => fn ($q) => $q->latest()->limit(12), 'conges' => fn ($q) => $q->latest()->limit(10)]);

        $droitConge = $this->payroll->droitCongeAnnuel($employe);
        $parts      = $this->payroll->quotientFamilial($employe);

        $stats = [
            'bulletins_count'    => $employe->bulletinsPaie()->count(),
            'conges_en_attente'  => $employe->conges()->where('statut', 'en_attente')->count(),
            'conges_pris_annee'  => $employe->conges()->where('statut', 'approuve')->where('type_conge', 'annuel')
                                        ->whereYear('date_debut', now()->year)->sum('nombre_jours'),
            'droit_conge_annuel' => $droitConge,
            'parts_fiscales'     => $parts,
        ];

        return view('rh.employes.show', compact('employe', 'stats', 'droitConge'));
    }

    public function edit(Employe $employe)
    {
        $services = Service::orderBy('nom')->get();
        return view('rh.employes.edit', compact('employe', 'services'));
    }

    public function update(Request $request, Employe $employe)
    {
        $validated = $request->validate($this->rules($employe->id));
        $employe->update($validated);

        return redirect()->route('rh.employes.show', $employe)
            ->with('success', 'Fiche employé mise à jour.');
    }

    public function destroy(Employe $employe)
    {
        $employe->delete();
        return redirect()->route('rh.employes.index')
            ->with('success', "Employé {$employe->nom_complet} archivé.");
    }

    // ── Créditer le solde de congé annuellement ───────────────────

    public function crediterConge(Employe $employe)
    {
        $droit = $this->payroll->droitCongeAnnuel($employe);
        $employe->increment('solde_conge', $droit);

        return back()->with('success', "{$droit} jour(s) de congé crédités à {$employe->nom_complet}.");
    }

    // ── Règles de validation ──────────────────────────────────────

    private function rules(?int $ignoreId = null): array
    {
        return [
            'matricule'              => "required|string|max:20|unique:employes,matricule,{$ignoreId}",
            'nom'                    => 'required|string|max:100',
            'prenom'                 => 'required|string|max:100',
            'date_naissance'         => 'nullable|date',
            'lieu_naissance'         => 'nullable|string|max:100',
            'nationalite'            => 'nullable|string|max:60',
            'sexe'                   => 'required|in:M,F',
            'situation_matrimoniale' => 'required|in:celibataire,marie,divorce,veuf',
            'nombre_enfants'         => 'required|integer|min:0|max:20',
            'telephone'              => 'nullable|string|max:20',
            'email'                  => "nullable|email|max:150|unique:employes,email,{$ignoreId}",
            'adresse'                => 'nullable|string',
            'ville'                  => 'nullable|string|max:100',
            'numero_cni'             => "nullable|string|max:50|unique:employes,numero_cni,{$ignoreId}",
            'numero_cnps'            => 'nullable|string|max:50',
            'numero_contribuable'    => 'nullable|string|max:50',
            'date_embauche'          => 'required|date',
            'type_contrat'           => 'required|in:CDI,CDD,stage,consultant',
            'date_fin_contrat'       => 'nullable|date|after:date_embauche',
            'poste'                  => 'required|string|max:150',
            'departement'            => 'nullable|string|max:100',
            'service_id'             => 'nullable|exists:services,id',
            'categorie_professionnelle' => 'nullable|string|max:20',
            'salaire_base'           => 'required|numeric|min:0',
            'statut'                 => 'required|in:actif,conge,suspendu,demissionne,licencie',
            'notes'                  => 'nullable|string',
        ];
    }
}
