<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $ticketInfo;
    public $ticketImagePath;

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param array $ticketInfo
     * @param string|null $ticketImagePath
     */
    public function __construct(Booking $booking, array $ticketInfo, ?string $ticketImagePath = null)
    {
        $this->booking = $booking;
        $this->ticketInfo = $ticketInfo;
        $this->ticketImagePath = $ticketImagePath;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Cảm ơn quý khách đã đặt vé tại CineBook - ' . $this->booking->booking_id_unique,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.booking-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        $attachments = [];
        
        // Attach PDF ticket nếu có
        if ($this->ticketImagePath && file_exists($this->ticketImagePath)) {
            $attachments[] = Attachment::fromPath($this->ticketImagePath)
                ->as('ticket-' . $this->booking->booking_id_unique . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
