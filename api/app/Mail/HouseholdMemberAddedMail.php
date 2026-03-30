<?php

namespace App\Mail;

use App\Models\Household;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HouseholdMemberAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $appUrl;

    public function __construct(
        public Household $household,
        public User $inviter,
        public User $addedUser,
    ) {
        $this->appUrl = rtrim(config('app.frontend_url') ?: config('app.url') ?: 'http://localhost', '/');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Du bist dem Haushalt „'.$this->household->name.'“ beigetreten',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.household-member-added',
        );
    }
}
