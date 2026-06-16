<?php

namespace App\Services;

use App\Models\BulletinPaie;
use App\Models\DeclarationTVA;
use App\Models\Depense;
use App\Models\Vente;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ImpotService
{
    // ── Taux légaux Cameroun (CGI) ────────────────────────────────
    const TVA_TAUX            = 0.1925;   // 19.25 %
    const IS_TAUX             = 0.30;     // 30 % du bénéfice imposable
    const IS_MINIMUM_TAUX     = 0.01;     // 1 % du CA
    const IS_MINIMUM_PLANCHER = 500_000;  // 500 000 FCFA plancher

    // Droit variable patente (art. 226 CGI)
    const PATENTE_DV_TAUX     = 0.00159;  // 0.159 % du CA
    const PATENTE_CAC_TAUX    = 0.10;     // 10 % centimes additionnels communaux

    private const MOIS_NOMS = [
        1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
        7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
    ];

    // ── Noms de mois ─────────────────────────────────────────────
    public function moisNom(int $mois): string
    {
        return self::MOIS_NOMS[$mois] ?? '';
    }

    // ── TVA ──────────────────────────────────────────────────────

    /** Importe automatiquement les données TVA depuis ventes et dépenses */
    public function importerDonneesTVA(int $mois, int $annee): array
    {
        // TVA collectée = somme montant_tva des ventes facturées/payées
        $ventesHT = (float) Vente::whereIn('statut', ['facturee', 'payee'])
            ->whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)
            ->sum('sous_total_ht');

        $tvaCollectee = (float) Vente::whereIn('statut', ['facturee', 'payee'])
            ->whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)
            ->sum('montant_tva');

        // TVA déductible = montant_ttc - montant_ht des dépenses approuvées/payées
        $depenses = Depense::whereIn('statut', ['approuvee', 'payee'])
            ->whereMonth('date_depense', $mois)->whereYear('date_depense', $annee)
            ->selectRaw('SUM(montant_ht) as total_ht, SUM(montant_ttc - montant_ht) as total_tva')
            ->first();

        $achatsHT      = (float) ($depenses->total_ht  ?? 0);
        $tvaDeductible = (float) ($depenses->total_tva ?? 0);

        // Crédit antérieur = credit_nouveau de la déclaration du mois précédent
        $moisPrec = $mois === 1 ? 12 : $mois - 1;
        $anneePrec = $mois === 1 ? $annee - 1 : $annee;
        $creditAnterieur = (float) (DeclarationTVA::where('periode_mois', $moisPrec)
            ->where('periode_annee', $anneePrec)
            ->value('credit_nouveau') ?? 0);

        return [
            'ventes_ht'        => $ventesHT,
            'tva_collectee'    => $tvaCollectee,
            'achats_ht'        => $achatsHT,
            'tva_deductible'   => $tvaDeductible,
            'credit_anterieur' => $creditAnterieur,
        ];
    }

    public function calculerTVA(array $data): array
    {
        $nette = $data['tva_collectee'] - $data['tva_deductible'] - ($data['credit_anterieur'] ?? 0);
        return [
            'tva_nette'       => $nette,
            'montant_a_payer' => $nette > 0 ? round($nette) : 0,
            'credit_nouveau'  => $nette < 0 ? round(abs($nette)) : 0,
        ];
    }

    public function echeanceTVA(int $mois, int $annee): Carbon
    {
        return Carbon::create($annee, $mois, 1)->addMonthNoOverflow()->day(15);
    }

    // ── IS (Impôt sur les Sociétés) ──────────────────────────────

    public function calculerIS(float $ca, float $benefice): array
    {
        $isBrut    = round($benefice * self::IS_TAUX);
        $minimumIS = (int) max(round($ca * self::IS_MINIMUM_TAUX), self::IS_MINIMUM_PLANCHER);
        $isDu      = max($isBrut, $minimumIS);
        return compact('isBrut', 'minimumIS', 'isDu');
    }

    /** Dates d'échéance des 4 acomptes IS (art. 26 CGI) */
    public function echeancesAcomptesIS(int $annee): array
    {
        return [
            1 => Carbon::create($annee, 2,  15),
            2 => Carbon::create($annee, 5,  15),
            3 => Carbon::create($annee, 8,  15),
            4 => Carbon::create($annee, 11, 15),
        ];
    }

    public function echeanceISAnnuelle(int $annee): Carbon
    {
        return Carbon::create($annee + 1, 4, 15);
    }

    /** Données annuelles consolidées depuis les modules existants */
    public function donneesAnnuelles(int $annee): array
    {
        $ca = (float) Vente::whereIn('statut', ['facturee', 'payee'])
            ->whereYear('date_vente', $annee)->sum('sous_total_ht');

        $charges = (float) Depense::whereIn('statut', ['approuvee', 'payee'])
            ->whereYear('date_depense', $annee)->sum('montant_ht');

        $masseSalariale = (float) BulletinPaie::where('annee', $annee)
            ->whereIn('statut', ['valide', 'paye'])->sum('salaire_brut');

        $cnpsEmployeur = (float) BulletinPaie::where('annee', $annee)
            ->whereIn('statut', ['valide', 'paye'])->sum('cotisation_cnps_employeur');

        return compact('ca', 'charges', 'masseSalariale', 'cnpsEmployeur');
    }

    // ── Patente ──────────────────────────────────────────────────

    public function calculerPatente(float $ca): array
    {
        $droitFixe    = $this->droitFixePatente($ca);
        $droitVariable = round($ca * self::PATENTE_DV_TAUX);
        $centimesAdditionnels = round(($droitFixe + $droitVariable) * self::PATENTE_CAC_TAUX);
        $montantTotal = $droitFixe + $droitVariable + $centimesAdditionnels;
        return compact('droitFixe', 'droitVariable', 'centimesAdditionnels', 'montantTotal');
    }

    private function droitFixePatente(float $ca): int
    {
        return match(true) {
            $ca <=   5_000_000 => 0,           // régime forfaitaire — exempté
            $ca <=  10_000_000 => 60_000,
            $ca <=  30_000_000 => 80_000,
            $ca <= 100_000_000 => 150_000,
            $ca <= 500_000_000 => 250_000,
            $ca <= 1_000_000_000 => 500_000,
            default              => 1_000_000,
        };
    }

    public function echeancePatente(int $annee): Carbon
    {
        return Carbon::create($annee, 3, 31);
    }

    // ── Calendrier fiscal (prochaines échéances) ─────────────────

    public function prochainEcheances(int $horizon = 90): Collection
    {
        $today = now()->startOfDay();
        $limit = $today->copy()->addDays($horizon);
        $annee = $today->year;
        $mois  = $today->month;

        $echeances = collect();

        // TVA — 3 prochains mois
        for ($i = 0; $i < 4; $i++) {
            $m = (($mois - 1 + $i) % 12) + 1;
            $a = $annee + intdiv($mois - 1 + $i, 12);
            $date = $this->echeanceTVA($m, $a);
            if ($date->between($today, $limit)) {
                $existante = DeclarationTVA::where('periode_mois', $m)->where('periode_annee', $a)->first();
                $echeances->push([
                    'type'    => 'TVA',
                    'label'   => 'TVA ' . $this->moisNom($m) . ' ' . $a,
                    'date'    => $date,
                    'statut'  => $existante?->statut ?? 'non_cree',
                    'couleur' => 'primary',
                    'lien'    => $existante ? route('impots.tva.show', $existante) : route('impots.tva.create'),
                ]);
            }
        }

        // IS — acomptes de l'année courante
        foreach ($this->echeancesAcomptesIS($annee) as $t => $date) {
            if ($date->between($today, $limit)) {
                $existant = \App\Models\DeclarationIS::where('type', 'acompte')
                    ->where('annee', $annee)->where('trimestre', $t)->first();
                $echeances->push([
                    'type'    => 'IS',
                    'label'   => 'Acompte IS T' . $t . ' ' . $annee,
                    'date'    => $date,
                    'statut'  => $existant?->statut ?? 'non_cree',
                    'couleur' => 'warning',
                    'lien'    => $existant ? route('impots.is.show', $existant) : route('impots.is.create'),
                ]);
            }
        }

        // IS annuelle
        $dateISAnnuelle = $this->echeanceISAnnuelle($annee - 1);
        if ($dateISAnnuelle->between($today, $limit)) {
            $existant = \App\Models\DeclarationIS::where('type', 'annuelle')->where('annee', $annee - 1)->first();
            $echeances->push([
                'type'    => 'IS',
                'label'   => 'IS Annuel ' . ($annee - 1),
                'date'    => $dateISAnnuelle,
                'statut'  => $existant?->statut ?? 'non_cree',
                'couleur' => 'danger',
                'lien'    => $existant ? route('impots.is.show', $existant) : route('impots.is.create'),
            ]);
        }

        // Patente
        $datePatente = $this->echeancePatente($annee);
        if ($datePatente->between($today, $limit)) {
            $existante = \App\Models\Patente::where('annee', $annee)->first();
            $echeances->push([
                'type'    => 'Patente',
                'label'   => 'Patente ' . $annee,
                'date'    => $datePatente,
                'statut'  => $existante?->statut ?? 'non_cree',
                'couleur' => 'info',
                'lien'    => $existante ? route('impots.patente.index') : route('impots.patente.create'),
            ]);
        }

        return $echeances->sortBy('date')->values();
    }
}
