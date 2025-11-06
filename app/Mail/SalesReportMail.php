<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Carbon\Carbon;

class SalesReportMail extends Mailable
{
    use Queueable, SerializesModels;

    // Propiedades públicas (siguen siendo necesarias)
    public $startDate;
    public $endDate;
    public $productSales;
    public $totalProductSalesAmount;
    public $totalProductSalesCount;
    public $membershipPayments;
    public $totalMembershipPaymentsAmount;
    public $totalMembershipPaymentsCount;
    public $cashMovements;
    public $totalCashEntries;
    public $totalCashExits;
    public $netCashMovement;
    public $grandTotal;
    public $gymName;
    public $fromAddress;

    /**
     * Create a new message instance.
     */
    public function __construct(array $reportData, string $fromAddress, string $fromName)
    {
        $this->startDate = $reportData['startDate'];
        $this->endDate = $reportData['endDate'];
        $this->productSales = $reportData['productSales'];
        $this->totalProductSalesAmount = $reportData['totalProductSalesAmount'];
        $this->totalProductSalesCount = $reportData['totalProductSalesCount'];
        $this->membershipPayments = $reportData['membershipPayments'];
        $this->totalMembershipPaymentsAmount = $reportData['totalMembershipPaymentsAmount'];
        $this->totalMembershipPaymentsCount = $reportData['totalMembershipPaymentsCount'];
        $this->cashMovements = $reportData['cashMovements'];
        $this->totalCashEntries = $reportData['totalCashEntries'];
        $this->totalCashExits = $reportData['totalCashExits'];
        $this->netCashMovement = $reportData['netCashMovement'];
        $this->grandTotal = $reportData['grandTotal'];
        $this->gymName = $fromName;
        $this->fromAddress = $fromAddress;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Reporte de Caja - ';
        if ($this->startDate->isSameDay($this->endDate)) {
            $subject .= $this->startDate->format('d/m/Y');
        } else {
            $subject .= $this->startDate->format('d/m/Y') . ' al ' . $this->endDate->format('d/m/Y');
        }
        $subject .= ' (' . $this->gymName . ')';

        return new Envelope(
            from: new Address($this->fromAddress, $this->gymName),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     * ***** MÉTODO MODIFICADO *****
     */
    public function content(): Content
    {
        // Pasamos explícitamente todas las variables a la vista
        return new Content(
            markdown: 'emails.sales-report',
            with: [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'productSales' => $this->productSales,
                'totalProductSalesAmount' => $this->totalProductSalesAmount,
                'totalProductSalesCount' => $this->totalProductSalesCount,
                'membershipPayments' => $this->membershipPayments,
                'totalMembershipPaymentsAmount' => $this->totalMembershipPaymentsAmount,
                'totalMembershipPaymentsCount' => $this->totalMembershipPaymentsCount,
                'cashMovements' => $this->cashMovements,
                'totalCashEntries' => $this->totalCashEntries,
                'totalCashExits' => $this->totalCashExits,
                'netCashMovement' => $this->netCashMovement,
                'grandTotal' => $this->grandTotal,
                'gymName' => $this->gymName,
                // 'fromAddress' no suele necesitarse en la vista, pero puedes añadirlo si quieres
            ],
        );
    }
    // ***** FIN MÉTODO MODIFICADO *****

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}