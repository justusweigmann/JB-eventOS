<?php

namespace HiEvents\Mail\Order;

use Barryvdh\DomPDF\Facade\Pdf;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\InvoiceDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Helper\Url;
use HiEvents\Mail\BaseMail;
use HiEvents\Services\Domain\Email\DTO\RenderedEmailTemplateDTO;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OrderSummary extends BaseMail
{
    private readonly ?RenderedEmailTemplateDTO $renderedTemplate;

    public function __construct(
        private readonly OrderDomainObject $order,
        private readonly EventDomainObject $event,
        private readonly OrganizerDomainObject $organizer,
        private readonly EventSettingDomainObject $eventSettings,
        private readonly ?InvoiceDomainObject $invoice,
        private readonly ?EventOccurrenceDomainObject $occurrence = null,
        ?RenderedEmailTemplateDTO $renderedTemplate = null,
    ) {
        $this->renderedTemplate = $renderedTemplate;

        parent::__construct();
    }

    public function envelope(): Envelope
    {
        $subject = $this->renderedTemplate?->subject
            ?? __('Your Order is Confirmed!') . ' 🎉';

        return new Envelope(
            from: new Address(
                address: (string) config('mail.from.address'),
                name: $this->getFromName(
                    $this->organizer,
                    $this->event,
                ),
            ),
            replyTo: $this->eventSettings->getSupportEmail(),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $headerData = $this->getMailHeaderData(
            $this->organizer,
        );

        logger()->warning('ORDER SUMMARY MAIL DATA', [
            'eventTitle' => $this->event->getTitle(),
            'eventId' => $this->event->getId(),
            'organizerName' => $this->organizer->getName(),
            'organizerWebsite' => $this->organizer->getWebsite(),
            'headerData' => $headerData,
        ]);

        if ($this->renderedTemplate) {
            return new Content(
                markdown: 'emails.custom-template',
                with: [
                    ...$headerData,
                    'renderedBody' => $this->renderedTemplate->body,
                    'renderedCta' => $this->renderedTemplate->cta,
                    'eventSettings' => $this->eventSettings,
                    'event' => $this->event,
                    'organizer' => $this->organizer,
                    'order' => $this->order,
                ],
            );
        }

        return new Content(
            markdown: 'emails.orders.summary',
            with: [
                ...$headerData,
                'eventSettings' => $this->eventSettings,
                'event' => $this->event,
                'order' => $this->order,
                'organizer' => $this->organizer,
                'occurrence' => $this->occurrence,
                'orderUrl' => sprintf(
                    Url::getFrontEndUrlFromConfig(
                        Url::ORDER_SUMMARY,
                    ),
                    $this->event->getId(),
                    $this->order->getShortId(),
                ),
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->invoice === null) {
            return [];
        }

        $invoice = Pdf::loadView('invoice', [
            'order' => $this->order,
            'event' => $this->event,
            'organizer' => $this->organizer,
            'eventSettings' => $this->eventSettings,
            'invoice' => $this->invoice,
        ]);

        return [
            Attachment::fromData(
                static fn () => $invoice->output(),
                'invoice.pdf',
            )->withMime('application/pdf'),
        ];
    }
}