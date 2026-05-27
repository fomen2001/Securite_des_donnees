<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = ['nom', 'description', 'couleur'];

    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'categorie_id');
    }

    public function getNbEquipementsAttribute(): int
    {
        return $this->equipements()->count();
    }
}
