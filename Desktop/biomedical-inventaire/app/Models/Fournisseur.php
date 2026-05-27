<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fournisseurs';

    protected $fillable = [
        'nom', 'contact_nom', 'telephone', 'email',
        'adresse', 'pays', 'site_web', 'statut',
    ];

    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'fournisseur_id');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'fournisseur_id');
    }
}
