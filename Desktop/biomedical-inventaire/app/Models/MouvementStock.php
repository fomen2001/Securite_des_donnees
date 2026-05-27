<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use HasFactory;

    protected $table = 'mouvements_stock';

    protected $fillable = [
        'equipement_id', 'type', 'quantite',
        'quantite_avant', 'quantite_apres',
        'service_source_id', 'service_destination_id',
        'reference_document', 'motif', 'user_id', 'date_mouvement',
    ];

    protected $casts = [
        'date_mouvement' => 'datetime',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class, 'equipement_id');
    }

    public function serviceSource()
    {
        return $this->belongsTo(Service::class, 'service_source_id');
    }

    public function serviceDestination()
    {
        return $this->belongsTo(Service::class, 'service_destination_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'entree'      => 'Entrée stock',
            'sortie'      => 'Sortie stock',
            'transfert'   => 'Transfert',
            'retour'      => 'Retour stock',
            'ajustement'  => 'Ajustement',
            'reforme'     => 'Réforme',
            default       => $this->type,
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'entree'      => 'success',
            'sortie'      => 'danger',
            'transfert'   => 'info',
            'retour'      => 'warning',
            'ajustement'  => 'secondary',
            'reforme'     => 'dark',
            default       => 'secondary',
        };
    }
}
