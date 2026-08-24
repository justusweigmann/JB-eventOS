<?php

namespace HiEvents\Mail;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\ImageDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Helper\Url;
use HiEvents\Repository\Interfaces\OrganizerRepositoryInterface;
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
                ?: $event->getTitle()
                ?: config('mail.from.name')
            )
        );
    }

    protected function getMailHeaderData(
        OrganizerDomainObject $organizer,
    ): array {
        $organizer = $this->loadOrganizerWithImages($organizer);
        $images = $organizer->getImages();

        $logo = $images?->first(
            static fn (ImageDomainObject $image): bool =>
                $image->getType() === 'ORGANIZER_LOGO'
        );

        $organizerLogoUrl = $logo
            ? Url::getCdnUrl($logo->getPath())
            : null;

        return [
            'mailOrganizerLogoUrl' => $organizerLogoUrl,
            'mailOrganizerName' => $organizer->getName(),
            'mailOrganizerWebsite' => $organizer->getWebsite(),
        ];
    }

    private function loadOrganizerWithImages(
        OrganizerDomainObject $organizer,
    ): OrganizerDomainObject {
        if ($organizer->getImages() !== null) {
            return $organizer;
        }

        $loadedOrganizer = app(OrganizerRepositoryInterface::class)
            ->loadRelation(ImageDomainObject::class)
            ->findById($organizer->getId());

        return $loadedOrganizer;
    }

    abstract public function envelope(): Envelope;

    abstract public function content(): Content;
}