<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom', 'batiment', 'etage', 'responsable', 'telephone'];

    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'service_id');
    }

    public function mouvementsSource()
    {
        return $this->hasMany(MouvementStock::class, 'service_source_id');
    }

    public function mouvementsDestination()
    {
        return $this->hasMany(MouvementStock::class, 'service_destination_id');
    }
}
