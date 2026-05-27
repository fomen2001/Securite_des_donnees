<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieDepense extends Model
{
    protected $table = 'categories_depenses';

    protected $fillable = [
        'nom', 'description', 'couleur', 'icone', 'type', 'est_deductible',
    ];

    protected $casts = [
        'est_deductible' => 'boolean',
    ];

    public function depenses()
    {
        return $this->hasMany(Depense::class, 'categorie_depense_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'exploitation'  => 'Exploitation',
            'investissement'=> 'Investissement',
            'financiere'    => 'Financière',
            default         => $this->type,
        };
    }
}
