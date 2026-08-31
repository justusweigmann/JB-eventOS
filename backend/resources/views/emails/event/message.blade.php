@php
    /** @var \HiEvents\DomainObjects\EventDomainObject $event */
    /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */
    /** @var \HiEvents\Services\Application\Handlers\Message\DTO\SendMessageDTO $messageData */
    /** @var ?string $mailOrganizerLogoUrl */
    /** @var ?string $mailOrganizerName */
    /** @var ?string $mailOrganizerWebsite */
@endphp

{{-- Header-Slot (Outlook-kompatibel) --}}
<x-slot:header>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f4f2f7;">
        <tr>
            <td align="center" style="padding-top:20px; padding-left:0; padding-right:0; padding-bottom:0;">
                {{-- Logo entfernt, da aktuell nicht zuverlässig darstellbar --}}
            </td>
        </tr>
    </table>
</x-slot:header>

<x-mail::message>
    {!! $messageData->message !!}

    {!! $eventSettings->getGetEmailFooterHtml() !!}

    <div style="color: #888; margin-top: 30px; font-size: .8em;">
        {{ __('You are receiving this communication because you are registered as an attendee for the following event:') }}

        <b>{{ $event->getTitle() }}</b>.

        {{ __('If you believe you have received this email in error,') }}
        {{ __('please contact the event organizer at') }}

        <a href="mailto:{{ $eventSettings->getSupportEmail() }}">
            {{ $eventSettings->getSupportEmail() }}
        </a>.

        {{ __('If you believe this is spam, please report it to') }}

        <a href="mailto:{{ config('mail.from.address') }}">
            {{ config('mail.from.address') }}
        </a>.
    </div>

    <x-slot:footer>
        <x-mail::footer>
            @if ($appEmailFooter = config('app.email_footer_text'))
                {{ $appEmailFooter }}
                <br><br>
            @endif

            © {{ date('Y') }} {{ config('app.name') }}
            |
            Powered by
            <a
                title="Manage events and sell tickets online with Hi.Events"
                href="https://hi.events?utm_source=app-email-footer"
            >
                Hi.Events
            </a>
        </x-mail::footer>
    </x-slot:footer>
</x-mail::message>