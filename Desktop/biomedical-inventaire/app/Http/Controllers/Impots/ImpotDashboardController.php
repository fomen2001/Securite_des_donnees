<?php

namespace App\Http\Controllers\Impots;

use App\Http\Controllers\Controller;
use App\Models\BilanComptable;
use App\Models\DeclarationIS;
use App\Models\DeclarationTVA;
use App\Models\Patente;
use App\Services\ImpotService;
use Carbon\Carbon;

class ImpotDashboardController extends Controller
{
    public function __construct(private ImpotService $impot) {}

    public function index()
    {
        $annee = now()->year;
        $mois  = now()->month;

        // Prochaines échéances (90 jours)
        $echeances = $this->impot->prochainEcheances(90);

        // TVA — statut des 12 derniers mois
        $tvaAnnee = DeclarationTVA::where('periode_annee', $annee)->get()->keyBy('periode_mois');
        $tvaMoisPrecedent = DeclarationTVA::where('periode_mois', $mois === 1 ? 12 : $mois - 1)
            ->where('periode_annee', $mois === 1 ? $annee - 1 : $annee)->first();

        // IS — acomptes de l'année
        $acomptesIS = DeclarationIS::where('type', 'acompte')->where('annee', $annee)
            ->orderBy('trimestre')->get()->keyBy('trimestre');
        $isAnnuel = DeclarationIS::where('type', 'annuelle')->where('annee', $annee - 1)->first();

        // Patente de l'année
        $patente = Patente::where('annee', $annee)->first();

        // Bilan du dernier exercice
        $bilan = BilanComptable::orderByDesc('exercice')->first();

        // TVA cumulée de l'année (payée)
        $tvaTotaleAnnee = DeclarationTVA::where('periode_annee', $annee)->sum('montant_a_payer');
        $isTotalAnnee   = DeclarationIS::where('annee', $annee)->sum('montant_a_payer');

        // Taux de conformité fiscal
        $obligationsTotal  = 12 + 4 + 1 + 1; // TVA mensuelle + acomptes IS + IS annuel + patente
        $obligationsRespectees = DeclarationTVA::where('periode_annee', $annee)->where('statut', 'payee')->count()
            + DeclarationIS::where('annee', $annee)->where('statut', 'payee')->count()
            + ($patente?->statut === 'payee' ? 1 : 0);
        $tauxConformite = $obligationsTotal > 0 ? round($obligationsRespectees / $obligationsTotal * 100) : 0;

        return view('impots.dashboard', compact(
            'annee', 'mois', 'echeances',
            'tvaAnnee', 'tvaMoisPrecedent',
            'acomptesIS', 'isAnnuel',
            'patente', 'bilan',
            'tvaTotaleAnnee', 'isTotalAnnee',
            'tauxConformite'
        ));
    }
}
