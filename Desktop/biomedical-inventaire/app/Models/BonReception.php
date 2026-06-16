<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BonReception extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'bons_reception';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'bon_commande_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "BR créé : {$this->numero}",
                'updated' => "BR modifié : {$this->numero}",
                default   => $e,
            });
    }

    protected $fillable = [
        'numero', 'bon_commande_id', 'fournisseur_id', 'user_id',
        'date_reception', 'statut', 'transporteur',
        'numero_bl_fournisseur', 'observations',
    ];

    protected $casts = [
        'date_reception' => 'date',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
    }

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
        return $this->hasMany(BonReceptionLigne::class);
    }

    // ── Accesseurs ───────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'valide'     => 'Validé',
            'rejete'     => 'Rejeté',
            default      => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'warning',
            'valide'     => 'success',
            'rejete'     => 'danger',
            default      => 'secondary',
        };
    }

    // ── Numérotation ─────────────────────────────────────────────

    public static function genererNumero(): string
    {
        $annee   = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('numero', 'like', "BR-{$annee}-%")
            ->orderByDesc('id')->first();
        $seq = $dernier ? (int) substr($dernier->numero, -4) + 1 : 1;
        return sprintf('BR-%d-%04d', $annee, $seq);
    }
}
