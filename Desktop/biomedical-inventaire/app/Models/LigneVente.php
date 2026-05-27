<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneVente extends Model
{
    protected $table = 'lignes_vente';

    protected $fillable = [
        'vente_id', 'equipement_id',
        'designation_snapshot', 'reference_snapshot',
        'quantite', 'prix_unitaire_ht', 'remise', 'total_ht',
    ];

    protected $casts = [
        'prix_unitaire_ht' => 'decimal:2',
        'remise'           => 'decimal:2',
        'total_ht'         => 'decimal:2',
    ];

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    // Recalcule le total HT de la ligne
    public function calculerTotal(): float
    {
        return round($this->quantite * $this->prix_unitaire_ht * (1 - $this->remise / 100), 2);
    }
}
