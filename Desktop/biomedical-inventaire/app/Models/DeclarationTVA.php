<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeclarationTVA extends Model
{
    protected $table = 'declarations_tva';

    protected $fillable = [
        'periode_mois', 'periode_annee', 'date_echeance',
        'ventes_ht', 'tva_collectee', 'achats_ht', 'tva_deductible',
        'credit_anterieur', 'tva_nette', 'credit_nouveau', 'montant_a_payer',
        'statut', 'date_paiement', 'reference_paiement', 'notes', 'document_path', 'created_by',
    ];

    protected $casts = [
        'date_echeance'  => 'date',
        'date_paiement'  => 'date',
    ];

    private const MOIS = [
        1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
        7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMoisNomAttribute(): string
    {
        return self::MOIS[$this->periode_mois] ?? '';
    }

    public function getPeriodeLabelAttribute(): string
    {
        return $this->mois_nom . ' ' . $this->periode_annee;
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

    /** Recalcule les montants à partir des champs bruts */
    public function recalculer(): void
    {
        $nette = $this->tva_collectee - $this->tva_deductible - $this->credit_anterieur;
        if ($nette > 0) {
            $this->tva_nette       = $nette;
            $this->montant_a_payer = $nette;
            $this->credit_nouveau  = 0;
        } else {
            $this->tva_nette       = $nette;
            $this->montant_a_payer = 0;
            $this->credit_nouveau  = abs($nette);
        }
    }
}
