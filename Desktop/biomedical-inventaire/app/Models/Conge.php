<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conge extends Model
{
    use HasFactory;

    protected $table = 'conges';

    protected $fillable = [
        'employe_id', 'type_conge', 'date_debut', 'date_fin',
        'nombre_jours', 'motif', 'statut',
        'approuve_par', 'date_approbation',
        'solde_avant', 'solde_apres',
        'document_path', 'motif_refus',
    ];

    protected $casts = [
        'date_debut'       => 'date',
        'date_fin'         => 'date',
        'date_approbation' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    // ── Accesseurs ────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type_conge) {
            'annuel'     => 'Congé annuel',
            'maladie'    => 'Congé maladie',
            'maternite'  => 'Congé maternité',
            'paternite'  => 'Congé paternité',
            'sans_solde' => 'Congé sans solde',
            'deuil'      => 'Congé de deuil',
            default      => 'Autre congé',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuve'   => 'Approuvé',
            'refuse'     => 'Refusé',
            'annule'     => 'Annulé',
            default      => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'warning',
            'approuve'   => 'success',
            'refuse'     => 'danger',
            'annule'     => 'secondary',
            default      => 'secondary',
        };
    }

    public function getDeduireduSoldeAttribute(): bool
    {
        return in_array($this->type_conge, ['annuel']);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouves($query)
    {
        return $query->where('statut', 'approuve');
    }
}
