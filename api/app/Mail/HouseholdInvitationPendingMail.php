<?php

namespace App\Mail;

use App\Models\Household;
use App\Models\HouseholdInvite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HouseholdInvitationPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $registerUrl;

    public string $acceptUrl;

    public function __construct(
        public HouseholdInvite $invite,
        public Household $household,
        public User $inviter,
    ) {
        $base = 'https://petfeeder.elias-englen.de';
        $this->registerUrl = $base.'/register?invite_token='.$invite->token;
        $this->acceptUrl = $base.'/invite/accept?token='.$invite->token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung: '.$this->household->name.' bei '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.household-invitation-pending',
        );
    }
}
