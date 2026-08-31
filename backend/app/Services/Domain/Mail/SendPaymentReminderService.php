<?php

namespace HiEvents\Services\Domain\Mail;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Mail\Order\PaymentReminderMail;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Mail\Mailer;

class SendPaymentReminderService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Mailer $mailer,
    ) {}

    public function sendPaymentReminderEmail(OrderDomainObject $order, int $hoursUntilExpiry): void
    {
        $order = $this->orderRepository
            ->loadRelation(new Relationship(
                domainObject: \HiEvents\DomainObjects\OrderItemDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: \HiEvents\DomainObjects\EventOccurrenceDomainObject::class,
                        nested: [
                            new Relationship(
                                domainObject: \HiEvents\DomainObjects\EventLocationDomainObject::class,
                                name: 'event_location',
                                nested: [
                                    new Relationship(
                                        domainObject: \HiEvents\DomainObjects\LocationDomainObject::class,
                                        name: 'location'
                                    ),
                                ],
                            ),
                        ],
                        name: 'event_occurrence',
                    ),
                ],
            ))
            ->findById($order->getId());

        $event = $this->eventRepository
            ->loadRelation(new Relationship(\HiEvents\DomainObjects\OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(new Relationship(\HiEvents\DomainObjects\EventSettingDomainObject::class))
            ->loadRelation(new Relationship(
                domainObject: \HiEvents\DomainObjects\EventLocationDomainObject::class,
                name: 'event_location',
                nested: [
                    new Relationship(
                        domainObject: \HiEvents\DomainObjects\LocationDomainObject::class,
                        name: 'location'
                    ),
                ]
            ))
            ->loadRelation(new Relationship(
                \HiEvents\DomainObjects\EventOccurrenceDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: \HiEvents\DomainObjects\EventLocationDomainObject::class,
                        name: 'event_location',
                        nested: [
                            new Relationship(
                                domainObject: \HiEvents\DomainObjects\LocationDomainObject::class,
                                name: 'location'
                            ),
                        ],
                    ),
                ]
            ))
            ->findById($order->getEventId());

        $organizer = $event->getOrganizer();
        $eventSettings = $event->getEventSettings();

        $paymentExpiryTimestamp = $order->getPaymentExpiryTimestamp();
        $paymentExpiryDate = date('Y-m-d H:i:s', $paymentExpiryTimestamp);

        $this->mailer
            ->to($order->getEmail())
            ->locale($order->getLocale())
            ->send(new PaymentReminderMail(
                order: $order,
                event: $event,
                eventSettings: $eventSettings,
                organizer: $organizer,
                paymentExpiryDate: $paymentExpiryDate,
                hoursUntilExpiry: $hoursUntilExpiry,
            ));
    }
}