<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommandeLigne extends Model
{
    protected $table = 'bon_commande_lignes';

    protected $fillable = [
        'bon_commande_id', 'equipement_id', 'designation', 'reference_fournisseur',
        'quantite_commandee', 'quantite_recue', 'unite',
        'prix_unitaire_ht', 'taux_tva', 'total_ht',
    ];

    protected $casts = [
        'quantite_commandee' => 'decimal:2',
        'quantite_recue'     => 'decimal:2',
        'prix_unitaire_ht'   => 'decimal:2',
        'taux_tva'           => 'decimal:2',
        'total_ht'           => 'decimal:2',
    ];

    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function getQuantiteRestanteAttribute(): float
    {
        return max(0, $this->quantite_commandee - $this->quantite_recue);
    }
}
