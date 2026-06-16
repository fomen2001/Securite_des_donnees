<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visiteur extends Model
{
    protected $fillable = [
        'nom', 'prenom', 'entreprise', 'telephone', 'email',
        'objet_visite', 'personne_visitee', 'employe_id',
        'date_entree', 'date_sortie', 'badge_numero',
        'statut', 'observations', 'user_id',
    ];

    protected $casts = [
        'date_entree' => 'datetime',
        'date_sortie' => 'datetime',
    ];

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'recu'       => 'Reçu',
            'sorti'      => 'Sorti',
            'annule'     => 'Annulé',
            default      => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'recu'       => 'primary',
            'sorti'      => 'success',
            'annule'     => 'danger',
            default      => 'secondary',
        };
    }

    public function getDureeAttribute(): ?string
    {
        if (!$this->date_sortie) return null;
        $diff = $this->date_entree->diff($this->date_sortie);
        if ($diff->h > 0) return $diff->h . 'h ' . str_pad($diff->i, 2, '0', STR_PAD_LEFT) . 'min';
        return $diff->i . ' min';
    }
}
