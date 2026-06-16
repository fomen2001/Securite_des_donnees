<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BonCommande extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'bons_commande';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'fournisseur_id', 'montant_ttc'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "BC créé : {$this->numero}",
                'updated' => "BC modifié : {$this->numero}",
                'deleted' => "BC supprimé : {$this->numero}",
                default   => $e,
            });
    }

    protected $fillable = [
        'numero', 'fournisseur_id', 'user_id', 'date_commande',
        'date_livraison_souhaitee', 'statut', 'montant_ht', 'taux_tva',
        'montant_tva', 'montant_ttc', 'conditions', 'notes',
    ];

    protected $casts = [
        'date_commande'            => 'date',
        'date_livraison_souhaitee' => 'date',
        'montant_ht'               => 'decimal:2',
        'taux_tva'                 => 'decimal:2',
        'montant_tva'              => 'decimal:2',
        'montant_ttc'              => 'decimal:2',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lignes()
    {
        return $this->hasMany(BonCommandeLigne::class);
    }

    public function receptions()
    {
        return $this->hasMany(BonReception::class);
    }

    // ── Accesseurs ───────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'brouillon'          => 'Brouillon',
            'confirmee'          => 'Confirmée',
            'partiellement_recue'=> 'Partiellement reçue',
            'recue'              => 'Reçue',
            'annulee'            => 'Annulée',
            default              => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'brouillon'           => 'secondary',
            'confirmee'           => 'primary',
            'partiellement_recue' => 'warning',
            'recue'               => 'success',
            'annulee'             => 'danger',
            default               => 'secondary',
        };
    }

    public function getTauxReceptionAttribute(): float
    {
        $commande = $this->lignes->sum('quantite_commandee');
        if ($commande == 0) return 0;
        $recu = $this->lignes->sum('quantite_recue');
        return round($recu / $commande * 100, 1);
    }

    // ── Numérotation ─────────────────────────────────────────────

    public static function genererNumero(): string
    {
        $annee   = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('numero', 'like', "BC-{$annee}-%")
            ->orderByDesc('id')->first();
        $seq = $dernier ? (int) substr($dernier->numero, -4) + 1 : 1;
        return sprintf('BC-%d-%04d', $annee, $seq);
    }

    // ── Totaux ───────────────────────────────────────────────────

    public function recalculerTotaux(): void
    {
        $ht  = $this->lignes->sum('total_ht');
        $tva = round($ht * $this->taux_tva / 100, 2);
        $this->update(['montant_ht' => $ht, 'montant_tva' => $tva, 'montant_ttc' => $ht + $tva]);
    }

    // ── Mise à jour statut selon réceptions ──────────────────────

    public function mettreAJourStatut(): void
    {
        $totalCmd = $this->lignes->sum('quantite_commandee');
        $totalRec = $this->lignes->sum('quantite_recue');

        if ($totalRec <= 0) {
            $statut = 'confirmee';
        } elseif ($totalRec >= $totalCmd) {
            $statut = 'recue';
        } else {
            $statut = 'partiellement_recue';
        }

        $this->update(['statut' => $statut]);
    }
}
