<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Organization $organization;

    public User $user;

    public function __construct(
        Organization $organization,
        User $user
    ) {
        $this->organization = $organization;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Flow CRM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-welcome',
            with: [
                'organization' => $this->organization,
                'user' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}