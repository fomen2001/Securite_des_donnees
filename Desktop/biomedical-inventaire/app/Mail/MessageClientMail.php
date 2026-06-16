<?php

namespace App\Mail;

use App\Models\MessageClient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MessageClient $message,
        public string $nomDestinataire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->message->objet);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.message-client');
    }

    public function attachments(): array
    {
        return $this->message->piecesJointes->map(function ($pj) {
            return Attachment::fromStorageDisk('local', $pj->chemin)
                ->as($pj->nom_original)
                ->withMime($pj->mime ?? 'application/octet-stream');
        })->all();
    }
}
