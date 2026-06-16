<?php

namespace App\Http\Controllers\Impots;

use App\Http\Controllers\Controller;
use App\Models\DeclarationIS;
use App\Services\ImpotService;
use Illuminate\Http\Request;

class DeclarationISController extends Controller
{
    public function __construct(private ImpotService $impot) {}

    public function index()
    {
        $declarations = DeclarationIS::orderByDesc('annee')
            ->orderByRaw("CASE type WHEN 'annuelle' THEN 0 ELSE trimestre END")
            ->paginate(20);

        foreach ($declarations as $d) {
            if ($d->estEnRetard() && $d->statut !== 'en_retard') {
                $d->update(['statut' => 'en_retard']);
            }
        }

        $totalPaye  = DeclarationIS::where('statut', 'payee')->where('annee', now()->year)->sum('montant_a_payer');
        $enRetard   = DeclarationIS::where('statut', 'en_retard')->count();

        return view('impots.is.index', compact('declarations', 'totalPaye', 'enRetard'));
    }

    public function create(Request $request)
    {
        $type   = $request->get('type', 'acompte');
        $annee  = (int) $request->get('annee', now()->year);
        $trim   = (int) $request->get('trimestre', 1);

        $donneesAnnuelles = $this->impot->donneesAnnuelles($annee);

        // Pour les acomptes, base = IS de l'année précédente
        $isAnneeN1 = DeclarationIS::where('type', 'annuelle')->where('annee', $annee - 1)->value('is_du') ?? 0;

        // Dates d'échéances
        $echeancesAcomptes = $this->impot->echeancesAcomptesIS($annee);
        $echeanceAnnuelle  = $this->impot->echeanceISAnnuelle($annee);

        // Acomptes déjà versés (pour déclaration annuelle)
        $acomptesVerses = DeclarationIS::where('type', 'acompte')->where('annee', $annee)
            ->where('statut', 'payee')->sum('montant_acompte');

        return view('impots.is.create', compact(
            'type', 'annee', 'trim',
            'donneesAnnuelles', 'isAnneeN1',
            'echeancesAcomptes', 'echeanceAnnuelle',
            'acomptesVerses'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'               => 'required|in:acompte,annuelle',
            'annee'              => 'required|integer|min:2020',
            'trimestre'          => 'nullable|integer|between:1,4',
            'chiffre_affaires'   => 'required|numeric|min:0',
            'benefice_imposable' => 'required|numeric',
            'base_acompte'       => 'nullable|numeric|min:0',
            'acomptes_verses'    => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $calcul = $this->impot->calculerIS($data['chiffre_affaires'], $data['benefice_imposable']);

        if ($data['type'] === 'acompte') {
            $baseAcompte    = $data['base_acompte'] ?? 0;
            $montantAcompte = round($baseAcompte / 4);
            $echeances      = $this->impot->echeancesAcomptesIS($data['annee']);
            $echeance       = $echeances[$data['trimestre']] ?? now();
            $montantAPayer  = $montantAcompte;
        } else {
            $baseAcompte    = 0;
            $montantAcompte = 0;
            $acomptesVerses = $data['acomptes_verses'] ?? 0;
            $echeance       = $this->impot->echeanceISAnnuelle($data['annee']);
            $montantAPayer  = max(0, $calcul['isDu'] - $acomptesVerses);
        }

        DeclarationIS::create([
            'type'               => $data['type'],
            'annee'              => $data['annee'],
            'trimestre'          => $data['type'] === 'acompte' ? $data['trimestre'] : null,
            'date_echeance'      => $echeance,
            'chiffre_affaires'   => $data['chiffre_affaires'],
            'benefice_imposable' => $data['benefice_imposable'],
            'is_brut'            => $calcul['isBrut'],
            'minimum_is'         => $calcul['minimumIS'],
            'is_du'              => $calcul['isDu'],
            'base_acompte'       => $baseAcompte,
            'montant_acompte'    => $montantAcompte,
            'acomptes_verses'    => $data['acomptes_verses'] ?? 0,
            'montant_a_payer'    => $montantAPayer,
            'statut'             => 'brouillon',
            'created_by'         => auth()->id(),
        ]);

        return redirect()->route('impots.is.index')->with('success', 'Déclaration IS enregistrée.');
    }

    public function show(DeclarationIS $is)
    {
        return view('impots.is.show', compact('is'));
    }

    public function soumettre(DeclarationIS $is)
    {
        abort_if($is->statut === 'payee', 403);
        $is->update(['statut' => 'soumise']);
        return back()->with('success', 'Déclaration IS soumise.');
    }

    public function payer(Request $request, DeclarationIS $is)
    {
        $data = $request->validate([
            'reference_paiement' => 'required|string|max:100',
            'date_paiement'      => 'required|date',
        ]);
        $is->update(array_merge($data, ['statut' => 'payee']));
        return back()->with('success', 'Paiement IS enregistré.');
    }
}
