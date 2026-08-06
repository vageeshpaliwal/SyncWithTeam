<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Invitation;
use App\Models\User;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
    */
    public Invitation $invitation;
    public function __construct(Invitation $invitation, )
    {
        $this->invitation = $invitation;
       
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User Invitation Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invitation',
            with: [
                'invitation' => $this->invitation,
             

            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
