<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Client créé : {$this->nom}",
                'updated' => "Client modifié : {$this->nom}",
                'deleted' => "Client supprimé : {$this->nom}",
                default   => $eventName,
            });
    }

    protected $fillable = [
        'code_client', 'nom', 'type', 'contact_nom',
        'telephone', 'email', 'adresse', 'ville', 'pays',
        'numero_contribuable', 'statut', 'notes',
    ];

    public function ventes()
    {
        return $this->hasMany(Vente::class, 'client_id');
    }

    public function getChiffreAffairesAttribute(): float
    {
        return $this->ventes()->whereIn('statut', ['payee', 'livree', 'facturee'])->sum('total_ttc');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'hopital'     => 'Hôpital',
            'clinique'    => 'Clinique',
            'cabinet'     => 'Cabinet médical',
            'laboratoire' => 'Laboratoire',
            'particulier' => 'Particulier',
            default       => 'Autre',
        };
    }

    public static function genererCode(): string
    {
        $dernier = self::withTrashed()->orderByDesc('id')->first();
        $sequence = $dernier ? (int) substr($dernier->code_client, -4) + 1 : 1;
        return sprintf('CLI-%04d', $sequence);
    }
}
