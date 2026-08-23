<?php

namespace HiEvents\Mail\Order;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Mail\BaseMail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/order-details-changed.blade.php
 */
class OrderDetailsChangedMail extends BaseMail
{
    public function __construct(
        private readonly EventDomainObject $event,
        private readonly OrganizerDomainObject $organizer,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly array $changedFields,
    ) {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: (string) config('mail.from.address'),
                name: $this->getFromName($this->organizer, $this->event),
            ),
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: __('Your Order Details Have Been Changed'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.order-details-changed',
            with: [
                'event' => $this->event,
                'organizer' => $this->organizer,
                'eventSettings' => $this->eventSettings,
                'changedFields' => $this->changedFields,
            ],
        );
    }
}