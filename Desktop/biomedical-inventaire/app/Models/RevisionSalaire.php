<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevisionSalaire extends Model
{
    protected $table = 'revisions_salaire';

    protected $fillable = [
        'employe_id', 'ancien_salaire', 'nouveau_salaire',
        'date_effet', 'motif', 'commentaire', 'approuve_par',
    ];

    protected $casts = [
        'date_effet'    => 'date',
        'ancien_salaire'=> 'decimal:2',
        'nouveau_salaire'=> 'decimal:2',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function getVariationAttribute(): float
    {
        if ($this->ancien_salaire == 0) return 0;
        return (($this->nouveau_salaire - $this->ancien_salaire) / $this->ancien_salaire) * 100;
    }

    public function getVariationFormatteeAttribute(): string
    {
        $v = $this->variation;
        $sign = $v >= 0 ? '+' : '';
        return "{$sign}" . number_format($v, 1) . ' %';
    }

    public function getMotifLabelAttribute(): string
    {
        return match ($this->motif) {
            'augmentation_merite' => 'Augmentation au mérite',
            'promotion'           => 'Promotion',
            'reclassement'        => 'Reclassement',
            'anciennete'          => 'Ancienneté',
            'revision_annuelle'   => 'Révision annuelle',
            default               => 'Autre',
        };
    }
}
