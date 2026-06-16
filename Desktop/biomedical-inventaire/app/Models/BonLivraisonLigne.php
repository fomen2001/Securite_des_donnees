<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonLigne extends Model
{
    protected $table = 'bon_livraison_lignes';

    protected $fillable = [
        'bon_livraison_id', 'equipement_id', 'designation',
        'reference', 'quantite', 'unite', 'observations',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
    ];

    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
}
