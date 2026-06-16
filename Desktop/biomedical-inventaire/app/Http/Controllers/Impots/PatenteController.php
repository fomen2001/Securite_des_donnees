<?php

namespace App\Http\Controllers\Impots;

use App\Http\Controllers\Controller;
use App\Models\Patente;
use App\Models\Vente;
use App\Services\ImpotService;
use Illuminate\Http\Request;

class PatenteController extends Controller
{
    public function __construct(private ImpotService $impot) {}

    public function index()
    {
        $patentes = Patente::orderByDesc('annee')->paginate(10);

        foreach ($patentes as $p) {
            if ($p->estEnRetard() && $p->statut !== 'en_retard') {
                $p->update(['statut' => 'en_retard']);
            }
        }

        return view('impots.patente.index', compact('patentes'));
    }

    public function create(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);

        // CA de référence = ventes de l'année précédente
        $caReference = (float) Vente::whereIn('statut', ['facturee', 'payee'])
            ->whereYear('date_vente', $annee - 1)->sum('sous_total_ht');

        $calcul   = $this->impot->calculerPatente($caReference);
        $echeance = $this->impot->echeancePatente($annee);

        $existante = Patente::where('annee', $annee)->first();

        return view('impots.patente.create', compact('annee', 'caReference', 'calcul', 'echeance', 'existante'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'annee'                      => 'required|integer|min:2020',
            'chiffre_affaires_reference' => 'required|numeric|min:0',
            'droit_fixe'                 => 'required|numeric|min:0',
            'droit_variable'             => 'required|numeric|min:0',
            'centimes_additionnels'      => 'required|numeric|min:0',
            'notes'                      => 'nullable|string|max:1000',
        ]);

        $echeance     = $this->impot->echeancePatente($data['annee']);
        $montantTotal = $data['droit_fixe'] + $data['droit_variable'] + $data['centimes_additionnels'];

        Patente::updateOrCreate(
            ['annee' => $data['annee']],
            array_merge($data, [
                'date_echeance' => $echeance,
                'montant_total' => $montantTotal,
                'statut'        => 'brouillon',
                'created_by'    => auth()->id(),
            ])
        );

        return redirect()->route('impots.patente.index')
            ->with('success', 'Patente enregistrée pour ' . $data['annee'] . '.');
    }

    public function soumettre(Patente $patente)
    {
        $patente->update(['statut' => 'soumise']);
        return back()->with('success', 'Patente soumise.');
    }

    public function payer(Request $request, Patente $patente)
    {
        $data = $request->validate([
            'reference_paiement' => 'required|string|max:100',
            'numero_quittance'   => 'nullable|string|max:100',
            'date_paiement'      => 'required|date',
        ]);
        $patente->update(array_merge($data, ['statut' => 'payee']));
        return back()->with('success', 'Paiement patente enregistré.');
    }

    /** Recalcule le montant en AJAX selon le CA saisi */
    public function calculer(Request $request)
    {
        $ca = (float) $request->input('ca', 0);
        return response()->json($this->impot->calculerPatente($ca));
    }
}
