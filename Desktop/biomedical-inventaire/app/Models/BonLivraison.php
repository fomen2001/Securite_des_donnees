<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BonLivraison extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'bons_livraison';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'client_id', 'vente_id'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "BL créé : {$this->numero}",
                'updated' => "BL modifié : {$this->numero}",
                default   => $e,
            });
    }

    protected $fillable = [
        'numero', 'vente_id', 'client_id', 'user_id', 'date_livraison',
        'statut', 'adresse_livraison', 'transporteur',
        'contact_reception', 'observations',
    ];

    protected $casts = [
        'date_livraison' => 'date',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

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
        return $this->hasMany(BonLivraisonLigne::class);
    }

    // ── Accesseurs ───────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'prepare'  => 'Préparé',
            'expedie'  => 'Expédié',
            'livre'    => 'Livré',
            'retourne' => 'Retourné',
            'annule'   => 'Annulé',
            default    => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'prepare'  => 'secondary',
            'expedie'  => 'info',
            'livre'    => 'success',
            'retourne' => 'warning',
            'annule'   => 'danger',
            default    => 'secondary',
        };
    }

    // ── Numérotation ─────────────────────────────────────────────

    public static function genererNumero(): string
    {
        $annee   = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('numero', 'like', "BL-{$annee}-%")
            ->orderByDesc('id')->first();
        $seq = $dernier ? (int) substr($dernier->numero, -4) + 1 : 1;
        return sprintf('BL-%d-%04d', $annee, $seq);
    }
}
