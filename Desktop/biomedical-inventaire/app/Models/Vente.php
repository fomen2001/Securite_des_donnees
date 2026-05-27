<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vente extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'montant_paye', 'mode_paiement', 'client_id', 'total_ttc'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Vente créée : {$this->numero_facture}",
                'updated' => "Vente modifiée : {$this->numero_facture}",
                'deleted' => "Vente supprimée : {$this->numero_facture}",
                default   => $eventName,
            });
    }

    protected $fillable = [
        'numero_facture', 'client_id', 'user_id', 'statut',
        'mode_paiement', 'date_vente', 'date_livraison_prevue',
        'date_livraison_reelle', 'date_echeance',
        'remise_globale', 'tva',
        'sous_total_ht', 'montant_remise', 'montant_tva', 'total_ttc',
        'montant_paye', 'conditions', 'notes',
    ];

    protected $casts = [
        'date_vente'             => 'date',
        'date_livraison_prevue'  => 'date',
        'date_livraison_reelle'  => 'date',
        'date_echeance'          => 'date',
        'remise_globale'         => 'decimal:2',
        'tva'                    => 'decimal:2',
        'sous_total_ht'          => 'decimal:2',
        'montant_remise'         => 'decimal:2',
        'montant_tva'            => 'decimal:2',
        'total_ttc'              => 'decimal:2',
        'montant_paye'           => 'decimal:2',
    ];

    // ---- Relations ----

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneVente::class, 'vente_id');
    }

    // ---- Accesseurs ----

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'brouillon'  => 'Brouillon',
            'confirmee'  => 'Confirmée',
            'livree'     => 'Livrée',
            'facturee'   => 'Facturée',
            'payee'      => 'Payée',
            'annulee'    => 'Annulée',
            default      => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'brouillon'  => 'secondary',
            'confirmee'  => 'info',
            'livree'     => 'primary',
            'facturee'   => 'warning',
            'payee'      => 'success',
            'annulee'    => 'danger',
            default      => 'secondary',
        };
    }

    public function getModePaiementLabelAttribute(): string
    {
        return match ($this->mode_paiement) {
            'especes'      => 'Espèces',
            'virement'     => 'Virement bancaire',
            'cheque'       => 'Chèque',
            'mobile_money' => 'Mobile Money',
            'credit'       => 'Crédit / Différé',
            default        => 'Autre',
        };
    }

    public function getResteAPayerAttribute(): float
    {
        return max(0, $this->total_ttc - $this->montant_paye);
    }

    public function getEstSoldeeAttribute(): bool
    {
        return $this->montant_paye >= $this->total_ttc;
    }

    // ---- Recalcul des totaux ----

    public function recalculerTotaux(): void
    {
        $sousTotal = $this->lignes->sum('total_ht');
        $remise    = round($sousTotal * $this->remise_globale / 100, 2);
        $baseHt    = $sousTotal - $remise;
        $tva       = round($baseHt * $this->tva / 100, 2);

        $this->update([
            'sous_total_ht'  => $sousTotal,
            'montant_remise' => $remise,
            'montant_tva'    => $tva,
            'total_ttc'      => $baseHt + $tva,
        ]);
    }

    // ---- Générateur de numéro de facture ----

    public static function genererNumero(): string
    {
        $annee   = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('numero_facture', 'like', "FAC-{$annee}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = $dernier ? (int) substr($dernier->numero_facture, -4) + 1 : 1;
        return sprintf('FAC-%d-%04d', $annee, $sequence);
    }

    // ---- Scopes ----

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', ['payee', 'annulee']);
    }

    public function scopeImpayees($query)
    {
        return $query->whereIn('statut', ['facturee', 'livree'])
            ->whereColumn('montant_paye', '<', 'total_ttc');
    }
}
