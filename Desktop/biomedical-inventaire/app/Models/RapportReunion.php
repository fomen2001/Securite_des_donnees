<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RapportReunion extends Model
{
    use SoftDeletes;

    protected $table = 'rapports_reunion';

    protected $fillable = [
        'reference', 'titre', 'date_reunion', 'lieu', 'type',
        'ordre_du_jour', 'compte_rendu', 'decisions', 'actions_a_suivre',
        'statut', 'user_id',
    ];

    protected $casts = [
        'date_reunion' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RapportReunionParticipant::class);
    }

    public static function genererReference(): string
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)->count();
        return 'RR-' . $annee . '-' . str_pad($dernier + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'interne'     => 'Réunion interne',
            'client'      => 'Avec client',
            'fournisseur' => 'Avec fournisseur',
            'partenaire'  => 'Avec partenaire',
            'autre'       => 'Autre',
            default       => $this->type,
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            'interne'     => 'primary',
            'client'      => 'success',
            'fournisseur' => 'warning',
            'partenaire'  => 'info',
            default       => 'secondary',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'finalise'  => 'Finalisé',
            default     => $this->statut,
        };
    }

    public function getNbPresentAttribute(): int
    {
        return $this->participants()->where('present', true)->count();
    }
}
