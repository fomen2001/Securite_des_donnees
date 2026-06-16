<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategorie extends Model
{
    protected $table = 'document_categories';

    protected $fillable = ['nom', 'couleur', 'icone', 'description'];

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_categorie_id');
    }
}
