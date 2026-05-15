<?php

namespace App\Mail;

use App\Models\EquipmentLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly EquipmentLoan $loan) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pembaruan Status Peminjaman [{$this->loan->loan_code}]",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-status-changed',
        );
    }
}
