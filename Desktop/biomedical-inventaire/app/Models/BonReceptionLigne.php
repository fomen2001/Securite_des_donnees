<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonReceptionLigne extends Model
{
    protected $table = 'bon_reception_lignes';

    protected $fillable = [
        'bon_reception_id', 'bon_commande_ligne_id', 'equipement_id',
        'designation', 'quantite_recue', 'quantite_conforme',
        'quantite_rejetee', 'motif_rejet',
    ];

    protected $casts = [
        'quantite_recue'     => 'decimal:2',
        'quantite_conforme'  => 'decimal:2',
        'quantite_rejetee'   => 'decimal:2',
    ];

    public function bonReception()
    {
        return $this->belongsTo(BonReception::class);
    }

    public function bonCommandeLigne()
    {
        return $this->belongsTo(BonCommandeLigne::class);
    }

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
}
