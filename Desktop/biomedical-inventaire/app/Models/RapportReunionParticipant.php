<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapportReunionParticipant extends Model
{
    protected $table = 'rapport_reunion_participants';

    protected $fillable = ['rapport_reunion_id', 'nom', 'fonction', 'entreprise', 'present'];

    protected $casts = ['present' => 'boolean'];

    public function rapport(): BelongsTo
    {
        return $this->belongsTo(RapportReunion::class, 'rapport_reunion_id');
    }
}
