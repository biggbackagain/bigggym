<?php

namespace App\Mail;

use App\Models\Member;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class WelcomeMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    public $member, $subscription, $gymName, $fromAddress, $fromName;

    public function __construct(Member $member, Subscription $subscription, $gymName, $fromAddress, $fromName)
    {
        $this->member = $member;
        $this->subscription = $subscription;
        $this->gymName = $gymName;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
    }

    public function envelope(): Envelope
    {
        $subject = $this->member->subscriptions()->count() <= 1 
            ? "¡Bienvenido(a) a {$this->gymName}!" 
            : "Confirmación de Renovación - {$this->gymName}";

        return new Envelope(from: new Address($this->fromAddress, $this->fromName), subject: $subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.welcome', with: [
            'member' => $this->member,
            'subscription' => $this->subscription,
            'gymName' => $this->gymName,
        ]);
    }
}