<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvanceSalaire extends Model
{
    protected $table = 'avances_salaire';

    protected $fillable = [
        'employe_id', 'montant', 'date_avance',
        'mois_deduction', 'annee_deduction', 'motif',
        'statut', 'approuve_par', 'date_approbation', 'bulletin_paie_id',
    ];

    protected $casts = [
        'date_avance'      => 'date',
        'date_approbation' => 'datetime',
        'montant'          => 'decimal:2',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function bulletinPaie()
    {
        return $this->belongsTo(BulletinPaie::class);
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente'  => 'warning',
            'approuvee'   => 'primary',
            'remboursee'  => 'success',
            'annulee'     => 'secondary',
            default       => 'secondary',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuvee'  => 'Approuvée',
            'remboursee' => 'Remboursée',
            'annulee'    => 'Annulée',
            default      => $this->statut,
        };
    }

    public function getMoisNomAttribute(): string
    {
        $mois = [
            1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',
            5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',
            9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
        ];
        return ($mois[$this->mois_deduction] ?? '') . ' ' . $this->annee_deduction;
    }
}
