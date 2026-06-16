<?php

namespace App\Http\Controllers\Impots;

use App\Http\Controllers\Controller;
use App\Models\DeclarationTVA;
use App\Services\ImpotService;
use Illuminate\Http\Request;

class DeclarationTVAController extends Controller
{
    public function __construct(private ImpotService $impot) {}

    public function index()
    {
        $declarations = DeclarationTVA::orderByDesc('periode_annee')
            ->orderByDesc('periode_mois')
            ->paginate(24);

        // Marquer en retard
        foreach ($declarations as $d) {
            if ($d->estEnRetard() && $d->statut !== 'en_retard') {
                $d->update(['statut' => 'en_retard']);
            }
        }

        $totalPaye    = DeclarationTVA::where('statut', 'payee')->whereYear('date_paiement', now()->year)->sum('montant_a_payer');
        $enAttente    = DeclarationTVA::whereIn('statut', ['brouillon', 'soumise'])->count();
        $creditTotal  = DeclarationTVA::latest()->value('credit_nouveau') ?? 0;

        return view('impots.tva.index', compact('declarations', 'totalPaye', 'enAttente', 'creditTotal'));
    }

    public function create(Request $request)
    {
        $mois  = (int) $request->get('mois', now()->month === 1 ? 12 : now()->month - 1);
        $annee = (int) $request->get('annee', now()->month === 1 ? now()->year - 1 : now()->year);

        // Vérifier si déclaration déjà créée
        if (DeclarationTVA::where('periode_mois', $mois)->where('periode_annee', $annee)->exists()) {
            return redirect()->route('impots.tva.index')
                ->with('error', "Une déclaration TVA existe déjà pour {$this->impot->moisNom($mois)} {$annee}.");
        }

        // Import automatique
        $donnees = $this->impot->importerDonneesTVA($mois, $annee);
        $calcul  = $this->impot->calculerTVA($donnees);
        $echeance = $this->impot->echeanceTVA($mois, $annee);

        $moisNom = $this->impot->moisNom($mois);

        return view('impots.tva.create', compact('mois', 'annee', 'moisNom', 'donnees', 'calcul', 'echeance'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'periode_mois'       => 'required|integer|between:1,12',
            'periode_annee'      => 'required|integer|min:2020',
            'ventes_ht'          => 'required|numeric|min:0',
            'tva_collectee'      => 'required|numeric|min:0',
            'achats_ht'          => 'required|numeric|min:0',
            'tva_deductible'     => 'required|numeric|min:0',
            'credit_anterieur'   => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
        ]);

        $data['credit_anterieur'] = $data['credit_anterieur'] ?? 0;
        $calcul = $this->impot->calculerTVA($data);
        $echeance = $this->impot->echeanceTVA($data['periode_mois'], $data['periode_annee']);

        $declaration = DeclarationTVA::create(array_merge($data, $calcul, [
            'date_echeance' => $echeance,
            'statut'        => 'brouillon',
            'created_by'    => auth()->id(),
        ]));

        return redirect()->route('impots.tva.show', $declaration)
            ->with('success', 'Déclaration TVA enregistrée.');
    }

    public function show(DeclarationTVA $tva)
    {
        return view('impots.tva.show', compact('tva'));
    }

    public function soumettre(DeclarationTVA $tva)
    {
        abort_if($tva->statut === 'payee', 403);
        $tva->update(['statut' => 'soumise']);
        return back()->with('success', 'Déclaration TVA soumise à la DGI.');
    }

    public function payer(Request $request, DeclarationTVA $tva)
    {
        $data = $request->validate([
            'reference_paiement' => 'required|string|max:100',
            'date_paiement'      => 'required|date',
        ]);
        $tva->update(array_merge($data, ['statut' => 'payee']));
        return back()->with('success', 'Paiement TVA enregistré.');
    }
}
