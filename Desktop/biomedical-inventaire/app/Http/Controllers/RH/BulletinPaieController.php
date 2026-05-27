<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class BulletinPaieController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index(Request $request)
    {
        $query = BulletinPaie::with('employe')->latest();

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('mois')) {
            $query->where('mois', $request->mois);
        }
        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $bulletins = $query->paginate(20)->withQueryString();
        $employes  = Employe::actifs()->orderBy('nom')->get();
        $annees    = range(now()->year, now()->year - 5);

        return view('rh.bulletins.index', compact('bulletins', 'employes', 'annees'));
    }

    public function create(Request $request)
    {
        $employes  = Employe::actifs()->orderBy('nom')->get();
        $employe   = $request->filled('employe_id') ? Employe::find($request->employe_id) : null;
        $mois      = $request->get('mois', now()->month);
        $annee     = $request->get('annee', now()->year);

        return view('rh.bulletins.create', compact('employes', 'employe', 'mois', 'annee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'            => 'required|exists:employes,id',
            'mois'                  => 'required|integer|between:1,12',
            'annee'                 => 'required|integer|min:2000',
            'jours_travailles'      => 'required|integer|between:1,31',
            'heures_supplementaires'=> 'nullable|numeric|min:0',
            'salaire_base'          => 'required|numeric|min:0',
            'total_primes'          => 'nullable|numeric|min:0',
            'total_indemnites'      => 'nullable|numeric|min:0',
            'avantages_nature'      => 'nullable|numeric|min:0',
            'avances_deduites'      => 'nullable|numeric|min:0',
            'mode_paiement'         => 'nullable|in:virement,especes,cheque,mobile_money',
            'observations'          => 'nullable|string',
            'details_primes'        => 'nullable|array',
            'details_indemnites'    => 'nullable|array',
        ]);

        $employe = Employe::findOrFail($request->employe_id);

        // Vérifier doublon
        $existe = BulletinPaie::where('employe_id', $employe->id)
            ->where('mois', $request->mois)
            ->where('annee', $request->annee)
            ->exists();
        if ($existe) {
            return back()->withErrors(['mois' => "Un bulletin existe déjà pour {$employe->nom_complet} en {$request->mois}/{$request->annee}."])->withInput();
        }

        // Calcul de la paie
        $params = $request->only([
            'salaire_base', 'total_primes', 'total_indemnites',
            'avantages_nature', 'avances_deduites',
        ]);
        $calcul = $this->payroll->calculer($params, $employe);

        $mois  = (int) $request->mois;
        $annee = (int) $request->annee;

        $bulletin = BulletinPaie::create([
            'numero'                    => BulletinPaie::genererNumero($mois, $annee),
            'employe_id'                => $employe->id,
            'mois'                      => $mois,
            'annee'                     => $annee,
            'periode_debut'             => "{$annee}-" . str_pad($mois, 2, '0', STR_PAD_LEFT) . '-01',
            'periode_fin'               => now()->setDate($annee, $mois, 1)->endOfMonth()->toDateString(),
            'jours_travailles'          => $request->jours_travailles,
            'heures_supplementaires'    => $request->heures_supplementaires ?? 0,
            'salaire_base'              => $request->salaire_base,
            'total_primes'              => $request->total_primes ?? 0,
            'total_indemnites'          => $request->total_indemnites ?? 0,
            'avantages_nature'          => $request->avantages_nature ?? 0,
            'salaire_brut'              => $calcul['salaire_brut'],
            'cotisation_cnps_salarie'   => $calcul['cotisation_cnps_salarie'],
            'cotisation_cnps_employeur' => $calcul['cotisation_cnps_employeur'],
            'irpp'                      => $calcul['irpp'],
            'cac'                       => $calcul['cac'],
            'rav'                       => $calcul['rav'],
            'avances_deduites'          => $calcul['avances_deduites'],
            'total_retenues'            => $calcul['total_retenues'],
            'net_a_payer'               => $calcul['net_a_payer'],
            'statut'                    => 'brouillon',
            'mode_paiement'             => $request->mode_paiement,
            'details_primes'            => $request->details_primes ?? [],
            'details_indemnites'        => $request->details_indemnites ?? [],
            'observations'              => $request->observations,
            'created_by'                => auth()->id(),
        ]);

        return redirect()->route('rh.bulletins.show', $bulletin)
            ->with('success', "Bulletin de paie généré : {$bulletin->numero}");
    }

    public function show(BulletinPaie $bulletin)
    {
        $bulletin->load('employe.service', 'createdBy');
        return view('rh.bulletins.show', compact('bulletin'));
    }

    public function valider(BulletinPaie $bulletin)
    {
        abort_if($bulletin->statut !== 'brouillon', 403, 'Ce bulletin ne peut plus être validé.');
        $bulletin->update(['statut' => 'valide']);
        return back()->with('success', 'Bulletin validé.');
    }

    public function payer(Request $request, BulletinPaie $bulletin)
    {
        abort_if($bulletin->statut === 'paye', 403, 'Bulletin déjà payé.');
        $request->validate([
            'mode_paiement' => 'required|in:virement,especes,cheque,mobile_money',
            'date_paiement' => 'required|date',
        ]);
        $bulletin->update([
            'statut'         => 'paye',
            'mode_paiement'  => $request->mode_paiement,
            'date_paiement'  => $request->date_paiement,
        ]);
        return back()->with('success', 'Paiement enregistré. Bulletin marqué comme payé.');
    }

    /** Aperçu JSON pour le calculateur temps-réel */
    public function simuler(Request $request)
    {
        $employe = Employe::findOrFail($request->employe_id);
        $calcul  = $this->payroll->simuler($request->only([
            'salaire_base', 'total_primes', 'total_indemnites',
            'avantages_nature', 'avances_deduites',
        ]), $employe);
        return response()->json($calcul);
    }
}
