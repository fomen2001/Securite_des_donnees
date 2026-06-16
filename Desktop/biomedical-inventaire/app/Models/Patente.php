<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patente extends Model
{
    protected $table = 'patentes';

    protected $fillable = [
        'annee', 'date_echeance',
        'chiffre_affaires_reference',
        'droit_fixe', 'droit_variable', 'centimes_additionnels', 'montant_total',
        'statut', 'date_paiement', 'reference_paiement', 'numero_quittance', 'notes', 'created_by',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_paiement' => 'date',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'brouillon'  => 'secondary',
            'soumise'    => 'primary',
            'payee'      => 'success',
            'en_retard'  => 'danger',
            default      => 'secondary',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon'  => 'Brouillon',
            'soumise'    => 'Soumise',
            'payee'      => 'Payée',
            'en_retard'  => 'En retard',
            default      => $this->statut,
        };
    }

    public function estEnRetard(): bool
    {
        return $this->statut !== 'payee' && $this->date_echeance->isPast();
    }
}
