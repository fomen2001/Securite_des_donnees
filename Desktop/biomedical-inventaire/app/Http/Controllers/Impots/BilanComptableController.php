<?php

namespace App\Http\Controllers\Impots;

use App\Http\Controllers\Controller;
use App\Models\BilanComptable;
use App\Models\BulletinPaie;
use App\Models\Depense;
use App\Models\Vente;
use App\Services\ImpotService;
use Illuminate\Http\Request;

class BilanComptableController extends Controller
{
    public function __construct(private ImpotService $impot) {}

    public function index()
    {
        $bilans = BilanComptable::orderByDesc('exercice')->paginate(10);
        return view('impots.bilan.index', compact('bilans'));
    }

    public function create(Request $request)
    {
        $exercice = (int) $request->get('exercice', now()->year - 1);

        // Pré-remplissage depuis les modules existants
        $ca = (float) Vente::whereIn('statut', ['facturee', 'payee'])
            ->whereYear('date_vente', $exercice)->sum('sous_total_ht');

        $charges = (float) Depense::whereIn('statut', ['approuvee', 'payee'])
            ->whereYear('date_depense', $exercice)->sum('montant_ht');

        $masseSalariale = (float) BulletinPaie::where('annee', $exercice)
            ->whereIn('statut', ['valide', 'paye'])->sum('salaire_brut');

        $cnpsEmployeur = (float) BulletinPaie::where('annee', $exercice)
            ->whereIn('statut', ['valide', 'paye'])->sum('cotisation_cnps_employeur');

        $chargesPersonnel = $masseSalariale + $cnpsEmployeur;

        // Données existantes si édition
        $existant = BilanComptable::where('exercice', $exercice)->first();

        return view('impots.bilan.create', compact(
            'exercice', 'ca', 'charges', 'chargesPersonnel', 'existant'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exercice'                   => 'required|integer|min:2020',
            // Actif
            'immob_incorporelles'        => 'nullable|numeric|min:0',
            'immob_corporelles'          => 'nullable|numeric|min:0',
            'immob_financieres'          => 'nullable|numeric|min:0',
            'stocks'                     => 'nullable|numeric|min:0',
            'creances_clients'           => 'nullable|numeric|min:0',
            'tva_recuperable'            => 'nullable|numeric|min:0',
            'autres_creances'            => 'nullable|numeric|min:0',
            'banques_caisse'             => 'nullable|numeric|min:0',
            // Passif
            'capital_social'             => 'nullable|numeric|min:0',
            'reserves'                   => 'nullable|numeric|min:0',
            'report_a_nouveau'           => 'nullable|numeric',
            'emprunts_long_terme'        => 'nullable|numeric|min:0',
            'autres_dettes_financieres'  => 'nullable|numeric|min:0',
            'dettes_fournisseurs'        => 'nullable|numeric|min:0',
            'dettes_fiscales'            => 'nullable|numeric|min:0',
            'dettes_sociales'            => 'nullable|numeric|min:0',
            'autres_dettes_court_terme'  => 'nullable|numeric|min:0',
            // Compte de résultat
            'chiffre_affaires'           => 'nullable|numeric|min:0',
            'autres_produits'            => 'nullable|numeric|min:0',
            'achats_consommes'           => 'nullable|numeric|min:0',
            'charges_personnel'          => 'nullable|numeric|min:0',
            'dotations_amortissements'   => 'nullable|numeric|min:0',
            'autres_charges_exploitation'=> 'nullable|numeric|min:0',
            'produits_financiers'        => 'nullable|numeric|min:0',
            'charges_financieres'        => 'nullable|numeric|min:0',
            'is_exerce'                  => 'nullable|numeric|min:0',
            'notes'                      => 'nullable|string|max:2000',
        ]);

        // Convertir les null en 0
        foreach ($data as $k => $v) {
            if ($v === null && $k !== 'notes' && $k !== 'exercice') {
                $data[$k] = 0;
            }
        }

        $bilan = new BilanComptable(array_merge($data, ['created_by' => auth()->id()]));
        $bilan->recalculerTotaux();

        BilanComptable::updateOrCreate(
            ['exercice' => $data['exercice']],
            $bilan->getAttributes()
        );

        return redirect()->route('impots.bilan.show', ['exercice' => $data['exercice']])
            ->with('success', 'Bilan comptable enregistré.');
    }

    public function show(int $exercice)
    {
        $bilan = BilanComptable::where('exercice', $exercice)->firstOrFail();
        return view('impots.bilan.show', compact('bilan'));
    }

    public function valider(int $exercice)
    {
        $bilan = BilanComptable::where('exercice', $exercice)->firstOrFail();
        $bilan->update(['statut' => 'valide', 'validated_by' => auth()->id()]);
        return back()->with('success', 'Bilan validé.');
    }

    public function deposer(Request $request, int $exercice)
    {
        $bilan = BilanComptable::where('exercice', $exercice)->firstOrFail();
        $data = $request->validate(['date_depot' => 'required|date']);
        $bilan->update(array_merge($data, ['statut' => 'depose']));
        return back()->with('success', 'DSF marquée comme déposée à la DGI.');
    }
}
