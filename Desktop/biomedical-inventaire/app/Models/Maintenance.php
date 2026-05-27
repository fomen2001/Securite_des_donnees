<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Maintenance extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['statut', 'type', 'technicien', 'cout', 'equipement_id', 'date_planifiee'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Maintenance planifiée sur équipement #{$this->equipement_id}",
                'updated' => "Maintenance mise à jour (statut: {$this->statut})",
                'deleted' => "Maintenance supprimée",
                default   => $eventName,
            });
    }

    protected $fillable = [
        'equipement_id', 'type', 'statut',
        'date_planifiee', 'date_debut', 'date_fin',
        'technicien', 'fournisseur_id',
        'description_travaux', 'observations',
        'cout', 'rapport_path', 'equipement_operationnel',
        'prochaine_maintenance', 'user_id',
    ];

    protected $casts = [
        'date_planifiee'        => 'date',
        'date_debut'            => 'date',
        'date_fin'              => 'date',
        'prochaine_maintenance' => 'date',
        'equipement_operationnel' => 'boolean',
        'cout'                  => 'decimal:2',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'preventive'   => 'Préventive',
            'corrective'   => 'Corrective',
            'calibration'  => 'Calibration',
            'verification' => 'Vérification',
            default        => $this->type,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'planifiee'  => 'info',
            'en_cours'   => 'warning',
            'terminee'   => 'success',
            'annulee'    => 'secondary',
            default      => 'secondary',
        };
    }

    public function getDureeAttribute(): ?int
    {
        if ($this->date_debut && $this->date_fin) {
            return $this->date_debut->diffInDays($this->date_fin);
        }
        return null;
    }
}
