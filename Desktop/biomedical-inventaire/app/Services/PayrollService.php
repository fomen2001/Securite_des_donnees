<?php

namespace App\Services;

use App\Models\Employe;

/**
 * Calcul de la paie selon la législation camerounaise.
 *
 * Textes de référence :
 *  - Loi n°92/007 du 14 août 1992 (Code du Travail)
 *  - Loi de finances – barème IRPP en vigueur
 *  - Décret CNPS : cotisation salarié 4,2 %, plafond 750 000 FCFA/mois
 */
class PayrollService
{
    // ── Constantes légales ────────────────────────────────────────

    /** SMIG mensuel (FCFA) */
    const SMIG = 41_875;

    /** Plafond assiette CNPS (FCFA/mois) */
    const CNPS_PLAFOND = 750_000;

    /** Taux cotisation CNPS salarié : vieillesse/invalidité/décès */
    const CNPS_TAUX_SALARIE = 0.042;

    /**
     * Taux cotisation CNPS employeur (global) :
     *  4,2 % vieillesse + 7 % allocations familiales + 1,75 % accidents travail
     */
    const CNPS_TAUX_EMPLOYEUR = 0.1295;

    /** Redevance audiovisuelle (FCFA/mois) */
    const RAV = 2_500;

    /** Taux CAC (Centimes Additionnels Communaux) sur l'IRPP */
    const CAC_TAUX = 0.10;

    /** Abattement professionnel : 30 % du revenu net, min/max annuels (FCFA) */
    const ABATTEMENT_TAUX   = 0.30;
    const ABATTEMENT_MIN    = 500_000;   // annuel
    const ABATTEMENT_MAX    = 2_500_000; // annuel

    // ── Barème IRPP (tranches annuelles, FCFA) ────────────────────

    const BAREME = [
        [0,          2_000_000,  0.10],
        [2_000_000,  3_000_000,  0.15],
        [3_000_000,  5_000_000,  0.25],
        [5_000_000,  10_000_000, 0.35],
        [10_000_000, PHP_FLOAT_MAX, 0.385],
    ];

    // ── Méthode principale ────────────────────────────────────────

    /**
     * Calcule tous les éléments du bulletin de paie.
     *
     * @param  array   $params  {salaire_base, total_primes, total_indemnites,
     *                           avantages_nature, avances_deduites}
     * @param  Employe $employe
     * @return array
     */
    public function calculer(array $params, Employe $employe): array
    {
        $salaire_base      = (float) ($params['salaire_base']      ?? 0);
        $total_primes      = (float) ($params['total_primes']      ?? 0);
        $total_indemnites  = (float) ($params['total_indemnites']  ?? 0);
        $avantages_nature  = (float) ($params['avantages_nature']  ?? 0);
        $avances_deduites  = (float) ($params['avances_deduites']  ?? 0);

        // 1. Salaire brut
        $salaire_brut = $salaire_base + $total_primes + $total_indemnites + $avantages_nature;

        // 2. CNPS salarié
        $base_cnps         = min($salaire_brut, self::CNPS_PLAFOND);
        $cnps_salarie      = round($base_cnps * self::CNPS_TAUX_SALARIE);
        $cnps_employeur    = round($base_cnps * self::CNPS_TAUX_EMPLOYEUR);

        // 3. IRPP mensuel
        $irpp = $this->calculerIRPP($salaire_brut, $cnps_salarie, $employe);

        // 4. CAC
        $cac = round($irpp * self::CAC_TAUX);

        // 5. RAV
        $rav = self::RAV;

        // 6. Totaux
        $total_retenues = $cnps_salarie + $irpp + $cac + $rav + $avances_deduites;
        $net_a_payer    = max(0, $salaire_brut - $total_retenues);

        return [
            'salaire_brut'              => round($salaire_brut),
            'cotisation_cnps_salarie'   => $cnps_salarie,
            'cotisation_cnps_employeur' => $cnps_employeur,
            'irpp'                      => $irpp,
            'cac'                       => $cac,
            'rav'                       => $rav,
            'avances_deduites'          => round($avances_deduites),
            'total_retenues'            => round($total_retenues),
            'net_a_payer'               => round($net_a_payer),
        ];
    }

    // ── IRPP ──────────────────────────────────────────────────────

    public function calculerIRPP(float $salaire_brut, float $cnps_salarie, Employe $employe): float
    {
        // Revenu net mensuel après CNPS
        $rn_mensuel = $salaire_brut - $cnps_salarie;

        // Abattement professionnel (30 %, plafonné)
        $abatt_brut    = $rn_mensuel * self::ABATTEMENT_TAUX;
        $abatt_min_m   = self::ABATTEMENT_MIN / 12;
        $abatt_max_m   = self::ABATTEMENT_MAX / 12;
        $abattement    = max($abatt_min_m, min($abatt_max_m, $abatt_brut));

        // Revenu net imposable annualisé
        $rni_annuel = max(0, ($rn_mensuel - $abattement) * 12);

        if ($rni_annuel <= 0) return 0;

        // Quotient familial
        $parts = $this->quotientFamilial($employe);

        // Impôt sur une part (barème progressif)
        $impot_une_part = $this->appliquerBareme($rni_annuel / $parts);

        // Impôt total
        $impot_annuel = $impot_une_part * $parts;

        // Crédit d'impôt (10 % du SMIG annuel)
        $credit = self::SMIG * 0.10 * 12;
        $impot_annuel = max(0, $impot_annuel - $credit);

        return round($impot_annuel / 12);
    }

    // ── Barème progressif ─────────────────────────────────────────

    private function appliquerBareme(float $revenu): float
    {
        if ($revenu <= 0) return 0;
        $impot = 0;
        foreach (self::BAREME as [$min, $max, $taux]) {
            if ($revenu <= $min) break;
            $impot += (min($revenu, $max) - $min) * $taux;
        }
        return $impot;
    }

    // ── Quotient familial ─────────────────────────────────────────

    public function quotientFamilial(Employe $employe): float
    {
        $parts = match ($employe->situation_matrimoniale) {
            'marie' => 2.0,
            'veuf'  => 1.5,
            default => 1.0,
        };
        // +0,5 par enfant à charge, maximum 6,5 parts au total
        $parts += min((int) $employe->nombre_enfants * 0.5, 4.5);
        return min($parts, 6.5);
    }

    // ── Droit à congé annuel (Code du Travail) ────────────────────

    /**
     * Calcule le nombre de jours de congé annuels dus.
     * Base : 1,5 j/mois = 18 j/an + bonification ancienneté.
     */
    public function droitCongeAnnuel(Employe $employe): int
    {
        $annees = (int) $employe->date_embauche->diffInYears(now());
        $base   = 18;
        $bonus  = match (true) {
            $annees >= 20 => 4,
            $annees >= 15 => 3,
            $annees >= 10 => 2,
            $annees >= 5  => 1,
            default       => 0,
        };
        return $base + $bonus;
    }

    // ── Simulation (pour l'aperçu temps-réel) ────────────────────

    /**
     * Retourne un tableau formaté pour les totaux du bulletin.
     * Utilisé par BulletinPaieController::simuler().
     */
    public function simuler(array $params, Employe $employe): array
    {
        return $this->calculer($params, $employe);
    }
}
