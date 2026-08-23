<?php

namespace HiEvents\Services\Domain\Invoice;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\InvoiceDomainObject;
use HiEvents\DomainObjects\OrderItemDomainObject;
use HiEvents\DomainObjects\Status\InvoiceStatus;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\InvoiceRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;

class InvoiceCreateService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InvoiceRepositoryInterface $invoiceRepository,
    ) {}

    public function createInvoiceForOrder(int $orderId): InvoiceDomainObject
    {
        $existingInvoice = $this->invoiceRepository->findFirstWhere([
            'order_id' => $orderId,
        ]);

        if ($existingInvoice) {
            return $existingInvoice;
        }

        $order = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->loadRelation(new Relationship(EventDomainObject::class, nested: [
                new Relationship(EventSettingDomainObject::class, name: 'event_settings'),
            ], name: 'event'))
            ->findById($orderId);

        /** @var EventSettingDomainObject $eventSettings */
        $eventSettings = $order->getEvent()->getEventSettings();
        /** @var EventDomainObject $event */
        $event = $order->getEvent();

        return $this->invoiceRepository->create([
            'order_id' => $orderId,
            'account_id' => $event->getAccountId(),
            'invoice_number' => $this->getLatestInvoiceNumber($event->getId(), $eventSettings),
            'items' => collect($order->getOrderItems())->map(fn (OrderItemDomainObject $item) => $item->toArray())->toArray(),
            'taxes_and_fees' => $order->getTaxesAndFeesRollup(),
            'issue_date' => now()->toDateString(),
            'status' => $order->isOrderCompleted() ? InvoiceStatus::PAID->name : InvoiceStatus::UNPAID->name,
            'total_amount' => $order->getTotalGross(),
            'due_date' => $eventSettings->getInvoicePaymentTermsDays() !== null
                ? now()->addDays($eventSettings->getInvoicePaymentTermsDays())
                : null,
        ]);
    }

    private function getLatestInvoiceNumber(
        int $eventId,
        EventSettingDomainObject $eventSettings
    ): string {
        $latestInvoice = $this->invoiceRepository
            ->findLatestInvoiceForEvent($eventId);

        $configuredStartNumber = $eventSettings->getInvoiceStartNumber();
        $configuredPrefix = $eventSettings->getInvoicePrefix();

        $startNumber = max(1, (int) ($configuredStartNumber ?? 1));
        $prefix = trim((string) ($configuredPrefix ?? ''));

        $startNumberString = trim((string) ($configuredStartNumber ?? '1'));

        if (! preg_match('/^\d+$/', $startNumberString)) {
            throw new \UnexpectedValueException(
                'Die Rechnungs-Startnummer darf nur aus Ziffern bestehen.'
            );
        }

        $numberLength = strlen($startNumberString);

        if (! $latestInvoice) {
            return $prefix . $this->formatInvoiceSequence(
                $startNumber,
                $numberLength
            );
        }

        $latestInvoiceNumber = trim((string) $latestInvoice->getInvoiceNumber());

        $numberPart = $latestInvoiceNumber;

        // Remove configured prefix if present
        if ($prefix !== '' && str_starts_with($numberPart, $prefix)) {
            $numberPart = substr($numberPart, strlen($prefix));
        }

        // Allow an optional separator like '-', '_' etc. directly after the prefix
        // e.g. "2026T-0001" or "2026T_0001" -> "0001"
        $numberPart = ltrim($numberPart, "-_");

        // Extract trailing digits if there are extra characters
        // e.g. "0001A" -> "0001", "2026T0001X" -> "0001"
        if (preg_match('/(\d+)$/', $numberPart, $matches)) {
            $numberPart = $matches[1];
        }

        if (! preg_match('/^\d+$/', $numberPart)) {
            // Fallback: ignore latest invoice and start from configured start number
            return $prefix . $this->formatInvoiceSequence(
                $startNumber,
                $numberLength
            );
        }

        $latestNumber = (int) $numberPart;

        if ($latestNumber < 0) {
            throw new \UnexpectedValueException(
                'Die letzte Rechnungsnummer darf nicht negativ sein.'
            );
        }

        $nextInvoiceNumber = $latestNumber + 1;

        return $prefix . $this->formatInvoiceSequence(
            $nextInvoiceNumber,
            $numberLength
        );
    }

    private function formatInvoiceSequence(
        int $number,
        int $minimumLength
    ): string {

        return str_pad(
            (string) $number,
            $minimumLength,
            '0',
            STR_PAD_LEFT
        );
    }
}
