<?php

namespace App\Mail;

use App\Models\Invitation; // Importe o nosso model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * A instância do convite.
     *
     * @var \App\Models\Invitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Você foi convidado para se juntar à TechnologySolutions!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // 1. Define a URL base do seu Angular
        // Idealmente isso viria do .env, mas pode deixar fixo por enquanto
        $frontendUrl = 'http://localhost:4200'; 

        // 2. Monta o link no formato que o Angular espera (?token=...)
        $link = $frontendUrl . '/register?token=' . $this->invitation->token;

        return new Content(
            view: 'emails.invitation',
            with: [
                'url' => $link, // <--- Enviamos a variável $url pronta para o HTML
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}