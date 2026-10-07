<?php

namespace App\Mail;

use App\Models\ClientRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class NewClientRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;

    public function __construct(ClientRegistration $registration)
    {
        $this->registration = $registration;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Registro de Cliente - ' . $this->registration->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-client-registration',
        );
    }

    public function attachments(): array
    {
        // Generar PDF
        $pdf = Pdf::loadView('emails.client-registration-pdf', [
            'registration' => $this->registration
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'registro-cliente.pdf')
                ->withMime('application/pdf'),
        ];
    }
}