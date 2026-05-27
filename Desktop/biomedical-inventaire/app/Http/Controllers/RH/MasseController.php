<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\AvanceSalaire;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class MasseController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    private const MOIS_NOMS = [
        1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
        7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
    ];

    /** Formulaire de génération en masse */
    public function create(Request $request)
    {
        $mois    = (int) $request->get('mois', now()->month);
        $annee   = (int) $request->get('annee', now()->year);
        $moisNom = self::MOIS_NOMS[$mois];

        $employes = Employe::actifs()->with('service')->orderBy('nom')->get();

        // Bulletins déjà générés pour ce mois, indexés par employe_id
        $bulletinsExistants = BulletinPaie::where('mois', $mois)
            ->where('annee', $annee)
            ->get()
            ->keyBy('employe_id');

        // Avances approuvées pour ce mois : montant indexé par employe_id
        $avancesModels = AvanceSalaire::where('mois_deduction', $mois)
            ->where('annee_deduction', $annee)
            ->where('statut', 'approuvee')
            ->get()
            ->keyBy('employe_id');

        $avances = $avancesModels->mapWithKeys(fn ($a) => [$a->employe_id => $a->montant]);

        // Simulation : attacher _net à chaque employé
        foreach ($employes as $e) {
            $avanceMontant = $avances->get($e->id, 0);
            $calcul = $this->payroll->calculer([
                'salaire_base'     => $e->salaire_base,
                'total_primes'     => 0,
                'total_indemnites' => 0,
                'avantages_nature' => 0,
                'avances_deduites' => $avanceMontant,
            ], $e);
            $e->_net = $calcul['net_a_payer'];
        }

        return view('rh.bulletins.masse', compact(
            'employes', 'bulletinsExistants', 'avances',
            'mois', 'annee', 'moisNom'
        ));
    }

    /** Génération en masse */
    public function store(Request $request)
    {
        $request->validate([
            'mois'         => 'required|integer|between:1,12',
            'annee'        => 'required|integer|min:2020',
            'employes'     => 'required|array|min:1',
            'employes.*'   => 'exists:employes,id',
            'mode_paiement'=> 'required|in:virement,especes,cheque,mobile_money',
        ]);

        $mois  = (int) $request->mois;
        $annee = (int) $request->annee;
        $generes = 0;
        $ignores = 0;

        $avances = AvanceSalaire::where('mois_deduction', $mois)
            ->where('annee_deduction', $annee)
            ->where('statut', 'approuvee')
            ->get()->keyBy('employe_id');

        foreach ($request->employes as $employeId) {
            // Vérifier doublon
            if (BulletinPaie::where('employe_id', $employeId)->where('mois', $mois)->where('annee', $annee)->exists()) {
                $ignores++;
                continue;
            }

            $employe = Employe::find($employeId);
            if (!$employe) continue;

            $avance = $avances->get($employeId)?->montant ?? 0;
            $params = [
                'salaire_base'     => $employe->salaire_base,
                'total_primes'     => 0,
                'total_indemnites' => 0,
                'avantages_nature' => 0,
                'avances_deduites' => $avance,
            ];

            $calcul = $this->payroll->calculer($params, $employe);

            $bulletin = BulletinPaie::create([
                'numero'                    => BulletinPaie::genererNumero($mois, $annee),
                'employe_id'                => $employe->id,
                'mois'                      => $mois,
                'annee'                     => $annee,
                'periode_debut'             => "{$annee}-" . str_pad($mois, 2, '0', STR_PAD_LEFT) . '-01',
                'periode_fin'               => now()->setDate($annee, $mois, 1)->endOfMonth()->toDateString(),
                'jours_travailles'          => 26,
                'heures_supplementaires'    => 0,
                'salaire_base'              => $employe->salaire_base,
                'total_primes'              => 0,
                'total_indemnites'          => 0,
                'avantages_nature'          => 0,
                'salaire_brut'              => $calcul['salaire_brut'],
                'cotisation_cnps_salarie'   => $calcul['cotisation_cnps_salarie'],
                'cotisation_cnps_employeur' => $calcul['cotisation_cnps_employeur'],
                'irpp'                      => $calcul['irpp'],
                'cac'                       => $calcul['cac'],
                'rav'                       => $calcul['rav'],
                'avances_deduites'          => $calcul['avances_deduites'],
                'total_retenues'            => $calcul['total_retenues'],
                'net_a_payer'               => $calcul['net_a_payer'],
                'statut'                    => 'valide',
                'mode_paiement'             => $request->mode_paiement,
                'created_by'                => auth()->id(),
            ]);

            // Marquer l'avance comme remboursée
            if ($avances->has($employeId)) {
                $avances->get($employeId)->update([
                    'statut'         => 'remboursee',
                    'bulletin_paie_id'=> $bulletin->id,
                ]);
            }

            $generes++;
        }

        return redirect()->route('rh.bulletins.index', ['mois' => $mois, 'annee' => $annee])
            ->with('success', "{$generes} bulletin(s) généré(s)" . ($ignores ? ", {$ignores} ignoré(s) (déjà existants)" : "") . ".");
    }
}
