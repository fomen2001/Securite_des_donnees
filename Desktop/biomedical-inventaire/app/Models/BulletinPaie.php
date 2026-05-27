<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulletinPaie extends Model
{
    use HasFactory;

    protected $table = 'bulletins_paie';

    protected $fillable = [
        'numero', 'employe_id', 'mois', 'annee',
        'periode_debut', 'periode_fin', 'jours_travailles', 'heures_supplementaires',
        'salaire_base', 'total_primes', 'total_indemnites', 'avantages_nature', 'salaire_brut',
        'cotisation_cnps_salarie', 'cotisation_cnps_employeur',
        'irpp', 'cac', 'rav', 'avances_deduites',
        'total_retenues', 'net_a_payer',
        'statut', 'mode_paiement', 'date_paiement',
        'details_primes', 'details_indemnites', 'observations', 'created_by',
    ];

    protected $casts = [
        'periode_debut'  => 'date',
        'periode_fin'    => 'date',
        'date_paiement'  => 'date',
        'details_primes'     => 'array',
        'details_indemnites' => 'array',
        'salaire_base'               => 'decimal:2',
        'total_primes'               => 'decimal:2',
        'total_indemnites'           => 'decimal:2',
        'avantages_nature'           => 'decimal:2',
        'salaire_brut'               => 'decimal:2',
        'cotisation_cnps_salarie'    => 'decimal:2',
        'cotisation_cnps_employeur'  => 'decimal:2',
        'irpp'                       => 'decimal:2',
        'cac'                        => 'decimal:2',
        'rav'                        => 'decimal:2',
        'avances_deduites'           => 'decimal:2',
        'total_retenues'             => 'decimal:2',
        'net_a_payer'                => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accesseurs ────────────────────────────────────────────────

    public function getMoisNomAttribute(): string
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
        return $mois[$this->mois] ?? '';
    }

    public function getPeriodeLabelAttribute(): string
    {
        return "{$this->mois_nom} {$this->annee}";
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'brouillon' => 'secondary',
            'valide'    => 'primary',
            'paye'      => 'success',
            default     => 'secondary',
        };
    }

    // ── Générateur de numéro ──────────────────────────────────────

    public static function genererNumero(int $mois, int $annee): string
    {
        $count = self::where('mois', $mois)->where('annee', $annee)->count() + 1;
        return sprintf('BUL-%04d-%02d-%04d', $annee, $mois, $count);
    }
}
