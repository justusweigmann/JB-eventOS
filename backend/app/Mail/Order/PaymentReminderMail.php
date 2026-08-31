<?php

namespace HiEvents\Mail\Order;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Mail\BaseMail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * @uses /backend/resources/views/emails/orders/payment-reminder.blade.php
 */
class PaymentReminderMail extends BaseMail
{
    public function __construct(
        private readonly OrderDomainObject $order,
        private readonly EventDomainObject $event,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly OrganizerDomainObject $organizer,
        private readonly string $paymentExpiryDate,
        private readonly int $hoursUntilExpiry,
    ) {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        $subject = $this->hoursUntilExpiry >= 72
            ? __('Payment reminder: Your order for :eventTitle is pending', ['eventTitle' => $this->event->getTitle()])
            : __('URGENT: Payment due in 24 hours for :eventTitle', ['eventTitle' => $this->event->getTitle()]);

        return new Envelope(
            from: new Address(
                address: (string) config('mail.from.address'),
                name: $this->getFromName($this->organizer, $this->event),
            ),
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.payment-reminder',
            with: [
                ...$this->getMailHeaderData($this->organizer),
                'event'             => $this->event,
                'order'             => $this->order,
                'organizer'         => $this->organizer,
                'eventSettings'     => $this->eventSettings,
                'paymentExpiryDate' => $this->paymentExpiryDate,
                'hoursUntilExpiry'  => $this->hoursUntilExpiry,
            ],
        );
    }
}