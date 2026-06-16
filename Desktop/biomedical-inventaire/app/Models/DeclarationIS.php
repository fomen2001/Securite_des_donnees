<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeclarationIS extends Model
{
    protected $table = 'declarations_is';

    protected $fillable = [
        'type', 'annee', 'trimestre', 'date_echeance',
        'chiffre_affaires', 'benefice_imposable',
        'is_brut', 'minimum_is', 'is_du',
        'base_acompte', 'montant_acompte',
        'acomptes_verses', 'montant_a_payer',
        'statut', 'date_paiement', 'reference_paiement', 'notes', 'document_path', 'created_by',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_paiement' => 'date',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        if ($this->type === 'annuelle') {
            return 'Déclaration annuelle IS ' . $this->annee;
        }
        return 'Acompte T' . $this->trimestre . ' — ' . $this->annee;
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
        return !in_array($this->statut, ['payee']) && $this->date_echeance->isPast();
    }
}
