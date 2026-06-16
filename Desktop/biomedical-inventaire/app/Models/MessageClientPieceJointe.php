<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageClientPieceJointe extends Model
{
    protected $table = 'message_pieces_jointes';

    protected $fillable = ['message_client_id', 'nom_original', 'chemin', 'mime', 'taille'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MessageClient::class, 'message_client_id');
    }

    public function getTailleLisibleAttribute(): string
    {
        $taille = $this->taille;
        if ($taille >= 1048576) return round($taille / 1048576, 1) . ' Mo';
        if ($taille >= 1024)    return round($taille / 1024, 0) . ' Ko';
        return $taille . ' o';
    }

    public function getIconeAttribute(): string
    {
        return match(true) {
            str_contains($this->mime, 'pdf')                      => 'bi-file-earmark-pdf text-danger',
            str_contains($this->mime, 'word')                     => 'bi-file-earmark-word text-primary',
            str_contains($this->mime, 'excel') ||
            str_contains($this->mime, 'spreadsheet')              => 'bi-file-earmark-excel text-success',
            str_contains($this->mime, 'image')                    => 'bi-file-earmark-image text-info',
            str_contains($this->mime, 'zip') ||
            str_contains($this->mime, 'compressed')               => 'bi-file-earmark-zip text-warning',
            default                                                => 'bi-file-earmark text-secondary',
        };
    }
}
