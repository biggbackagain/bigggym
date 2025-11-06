<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use App\Models\Member;

class WelcomeMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member;
    public $gymName;
    public $fromAddress;
    public $fromName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Member $member, $gymName, $fromAddress, $fromName)
    {
        $this->member = $member;
        $this->gymName = $gymName;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: '¡Bienvenido(a) a ' . $this->gymName . '!', // Asunto del correo
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome', // Vista que usaremos
            with: [
                'memberName' => $this->member->name,
                'gymName' => $this->gymName,
                // Puedes pasar más datos si los necesitas en la plantilla
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