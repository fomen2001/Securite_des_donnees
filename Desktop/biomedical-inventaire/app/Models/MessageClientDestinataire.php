<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageClientDestinataire extends Model
{
    protected $table = 'message_client_destinataires';

    protected $fillable = [
        'message_client_id', 'client_id',
        'email_copie', 'telephone_copie',
        'statut_email', 'statut_sms',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MessageClient::class, 'message_client_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
