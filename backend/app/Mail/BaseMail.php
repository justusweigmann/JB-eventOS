<?php

namespace HiEvents\Mail;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Helper\Url;
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
        $images = $organizer->getImages();

        $logo = $images?->first(
            static function ($image): bool {
                return $image->getType() === 'ORGANIZER_LOGO';
            }
        );

        $organizerLogoUrl = $logo
            ? Url::getCdnUrl($logo->getPath())
            : null;

        logger()->warning('ORGANIZER MAIL HEADER DATA', [
            'organizerName' => $organizer->getName(),
            'organizerWebsite' => $organizer->getWebsite(),
            'organizerLogoUrl' => $organizerLogoUrl,
            'organizerLogoPath' => $logo?->getPath(),
            'imagesLoaded' => $images !== null,
            'imagesCount' => $images?->count(),
        ]);

        return [
            'mailOrganizerLogoUrl' => $organizerLogoUrl,
            'mailOrganizerName' => $organizer->getName(),
            'mailOrganizerWebsite' => $organizer->getWebsite(),
        ];
    }

    abstract public function envelope(): Envelope;

    abstract public function content(): Content;
}