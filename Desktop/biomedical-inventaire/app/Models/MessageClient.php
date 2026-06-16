<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MessageClientPieceJointe;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageClient extends Model
{
    use SoftDeletes;

    protected $table = 'messages_clients';

    protected $fillable = [
        'reference', 'objet', 'corps', 'canal', 'statut', 'envoye_le', 'user_id',
    ];

    protected $casts = [
        'envoye_le' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(MessageClientDestinataire::class);
    }

    public function piecesJointes(): HasMany
    {
        return $this->hasMany(MessageClientPieceJointe::class);
    }

    public static function genererReference(): string
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)->count();
        return 'MSG-' . $annee . '-' . str_pad($dernier + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getCanalLabelAttribute(): string
    {
        return match($this->canal) {
            'email'     => 'Email',
            'sms'       => 'SMS',
            'email_sms' => 'Email + SMS',
            default     => $this->canal,
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon'             => 'Brouillon',
            'envoye'                => 'Envoyé',
            'partiellement_envoye'  => 'Partiellement envoyé',
            'echoue'                => 'Échoué',
            default                 => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'brouillon'            => 'secondary',
            'envoye'               => 'success',
            'partiellement_envoye' => 'warning',
            'echoue'               => 'danger',
            default                => 'secondary',
        };
    }
}
