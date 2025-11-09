<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class SaleReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    // Propiedades públicas para pasar los datos a la vista
    public $sale;
    public $gymName;
    public $fromAddress;
    public $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, string $fromAddress, string $fromName)
    {
        $this->sale = $sale;
        $this->gymName = $fromName;
        $this->fromAddress = $fromAddress;
        $this->fromName = $fromName;
        
        // Cargar la relación con los productos si no está cargada
        $this->sale->loadMissing('products');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: 'Comprobante de Venta de ' . $this->gymName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Pasamos todas las propiedades públicas automáticamente a la vista
        return new Content(
            markdown: 'emails.sale-receipt',
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