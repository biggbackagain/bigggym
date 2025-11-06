<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use App\Models\Member; // <-- AÑADIR IMPORT

class GymPromotionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customSubject;
    public $customMessage;
    public $fromAddress;
    public $fromName;
    public $memberName; // <-- AÑADIR PROPIEDAD PARA EL NOMBRE

    /**
     * Create a new message instance.
     */
    // Modificar constructor para aceptar el miembro
    public function __construct($subject, $message, $fromAddress, $fromName, Member $member) // <-- AÑADIR Member $member
    {
        $this->customSubject = $subject;
        $this->customMessage = $message;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        $this->memberName = $member->name; // <-- GUARDAR EL NOMBRE DEL MIEMBRO
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: $this->customSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Pasar el nombre del miembro a la vista
        return new Content(
            markdown: 'emails.promotion',
            with: [
                'messageContent' => $this->customMessage,
                'memberName' => $this->memberName, // <-- PASAR EL NOMBRE A LA VISTA
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