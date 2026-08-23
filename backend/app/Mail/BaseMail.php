<?php

namespace HiEvents\Mail;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class BaseMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->afterCommit();
    }

    protected function getFromName(
        OrganizerDomainObject $organizer,
        EventDomainObject $event,
    ): string {
        return trim(
            (string) (
                $organizer->getName()
                ?: $event->getName()
                ?: config('mail.from.name')
            )
        );
    }

    abstract public function envelope(): Envelope;

    abstract public function content(): Content;
}