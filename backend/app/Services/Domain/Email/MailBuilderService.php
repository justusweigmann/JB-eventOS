<?php

namespace HiEvents\Services\Domain\Email;

use Carbon\Carbon;
use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\Enums\EmailTemplateType;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\ImageDomainObject;
use HiEvents\DomainObjects\InvoiceDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrderItemDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Helper\DateHelper;
use HiEvents\Helper\Url;
use HiEvents\Mail\Attendee\AttendeeTicketMail;
use HiEvents\Mail\Occurrence\OccurrenceCancellationMail;
use HiEvents\Mail\Order\OrderSummary;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\OrganizerRepositoryInterface;
use HiEvents\Services\Domain\Email\DTO\RenderedEmailTemplateDTO;
use HiEvents\Services\Domain\Order\OfflinePaymentInstructionsRenderService;
use Illuminate\Support\Facades\Log;

class MailBuilderService
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly EmailTokenContextBuilder $tokenContextBuilder,
        private readonly OfflinePaymentInstructionsRenderService $offlinePaymentInstructionsRenderService,
        private readonly OrganizerRepositoryInterface $organizerRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function buildAttendeeTicketMail(
        AttendeeDomainObject $attendee,
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        ?EventOccurrenceDomainObject $occurrence = null,
    ): AttendeeTicketMail {
        $order = $this->loadFreshOrder($order);
        $organizer = $this->loadOrganizerImages($organizer);

        Log::debug('MailBuilderService::buildAttendeeTicketMail - before render', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        $renderedTemplate = $this->renderAttendeeTicketTemplate(
            $attendee,
            $order,
            $event,
            $eventSettings,
            $organizer,
            $occurrence,
        );

        Log::debug('MailBuilderService::buildAttendeeTicketMail - before new AttendeeTicketMail', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        return new AttendeeTicketMail(
            order: $order,
            attendee: $attendee,
            event: $event,
            eventSettings: $eventSettings,
            organizer: $organizer,
            renderedTemplate: $renderedTemplate,
            occurrence: $occurrence,
        );
    }

    public function buildOrderSummaryMail(
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        ?InvoiceDomainObject $invoice = null,
        ?EventOccurrenceDomainObject $occurrence = null,
    ): OrderSummary {
        $order = $this->loadFreshOrder($order);
        $organizer = $this->loadOrganizerImages($organizer);

        Log::debug('MailBuilderService::buildOrderSummaryMail - before render', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        $renderedTemplate = $this->renderOrderSummaryTemplate(
            $order,
            $event,
            $eventSettings,
            $organizer,
            $occurrence,
        );

        if (! $renderedTemplate) {
            $this->offlinePaymentInstructionsRenderService->render(
                $order,
                $event,
                $organizer,
                $eventSettings,
            );
        }

        Log::debug('MailBuilderService::buildOrderSummaryMail - before new OrderSummary', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        return new OrderSummary(
            order: $order,
            event: $event,
            organizer: $organizer,
            eventSettings: $eventSettings,
            invoice: $invoice,
            occurrence: $occurrence,
            renderedTemplate: $renderedTemplate,
        );
    }

    public function buildOccurrenceCancellationMail(
        EventDomainObject $event,
        EventOccurrenceDomainObject $occurrence,
        OrganizerDomainObject $organizer,
        EventSettingDomainObject $eventSettings,
        bool $refundOrders = false,
    ): OccurrenceCancellationMail {
        $organizer = $this->loadOrganizerImages($organizer);

        Log::debug('MailBuilderService::buildOccurrenceCancellationMail - before render', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        $renderedTemplate = $this->renderOccurrenceCancellationTemplate(
            $event,
            $occurrence,
            $eventSettings,
            $organizer,
            $refundOrders,
        );

        $startDate = DateHelper::convertFromUTC(
            $occurrence->getStartDate(),
            $event->getTimezone(),
        );

        $formattedDate = (new Carbon($startDate))
            ->format('F j, Y g:i A');

        Log::debug('MailBuilderService::buildOccurrenceCancellationMail - before new OccurrenceCancellationMail', [
            'organizerId'   => $organizer->getId(),
            'organizerName' => $organizer->getName(),
            'organizerEmail' => $organizer->getEmail(),
            'organizerImagesCount' => $organizer->getImages()?->count(),
        ]);

        return new OccurrenceCancellationMail(
            event: $event,
            occurrence: $occurrence,
            organizer: $organizer,
            eventSettings: $eventSettings,
            formattedDate: $formattedDate,
            refundOrders: $refundOrders,
            renderedTemplate: $renderedTemplate,
        );
    }

    private function loadFreshOrder(
        OrderDomainObject $order,
    ): OrderDomainObject {
        $freshOrder = $this->orderRepository
            ->loadRelation(OrderItemDomainObject::class)
            ->findById($order->getId());
        return $freshOrder;
    }

    private function loadOrganizerImages(
        OrganizerDomainObject $organizer,
    ): OrganizerDomainObject {
        $loadedOrganizer = $this->organizerRepository
            ->loadRelation(ImageDomainObject::class)
            ->findById($organizer->getId());

        $images = $loadedOrganizer->getImages();

        $logo = $images?->first(
            static fn (ImageDomainObject $image): bool =>
                $image->getType() === 'ORGANIZER_LOGO'
        );

        $organizerLogoUrl = $logo
            ? Url::getCdnUrl($logo->getPath())
            : null;

        Log::debug('MailBuilderService::loadOrganizerImages', [
            'organizerId'        => $organizer->getId(),
            'organizerName'      => $loadedOrganizer->getName(),
            'organizerWebsite'   => $loadedOrganizer->getWebsite(),
            'organizerLogoUrl'   => $organizerLogoUrl,
            'imagesCount'        => $images?->count(),
            'imageTypes'         => $images?->map(fn ($i) => $i->getType())->toArray(),
            'logoFound'          => $logo !== null,
        ]);

        return $loadedOrganizer;
    }

    private function renderAttendeeTicketTemplate(
        AttendeeDomainObject $attendee,
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        ?EventOccurrenceDomainObject $occurrence = null,
    ): ?RenderedEmailTemplateDTO {
        $template = $this->emailTemplateService->getTemplateByType(
            type: EmailTemplateType::ATTENDEE_TICKET,
            accountId: $event->getAccountId(),
            eventId: $event->getId(),
            organizerId: $organizer->getId(),
        );

        if (! $template) {
            return null;
        }

        $context = $this->tokenContextBuilder->buildAttendeeTicketContext(
            $attendee,
            $order,
            $event,
            $organizer,
            $eventSettings,
            $occurrence,
        );

        return $this->emailTemplateService->renderTemplate(
            $template,
            $context,
        );
    }

    private function renderOrderSummaryTemplate(
        OrderDomainObject $order,
        EventDomainObject $event,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        ?EventOccurrenceDomainObject $occurrence = null,
    ): ?RenderedEmailTemplateDTO {
        $template = $this->emailTemplateService->getTemplateByType(
            type: EmailTemplateType::ORDER_CONFIRMATION,
            accountId: $event->getAccountId(),
            eventId: $event->getId(),
            organizerId: $organizer->getId(),
        );

        if (! $template) {
            return null;
        }

        $context = $this->tokenContextBuilder->buildOrderConfirmationContext(
            $order,
            $event,
            $organizer,
            $eventSettings,
            $occurrence,
        );

        return $this->emailTemplateService->renderTemplate(
            $template,
            $context,
        );
    }

    private function renderOccurrenceCancellationTemplate(
        EventDomainObject $event,
        EventOccurrenceDomainObject $occurrence,
        EventSettingDomainObject $eventSettings,
        OrganizerDomainObject $organizer,
        bool $refundOrders = false,
    ): ?RenderedEmailTemplateDTO {
        $template = $this->emailTemplateService->getTemplateByType(
            type: EmailTemplateType::OCCURRENCE_CANCELLATION,
            accountId: $event->getAccountId(),
            eventId: $event->getId(),
            organizerId: $organizer->getId(),
        );

        if (! $template) {
            return null;
        }

        $context = $this->tokenContextBuilder->buildOccurrenceCancellationContext(
            $event,
            $occurrence,
            $organizer,
            $eventSettings,
            $refundOrders,
        );

        return $this->emailTemplateService->renderTemplate(
            $template,
            $context,
        );
    }
}