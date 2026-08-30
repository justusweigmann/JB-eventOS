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
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#ffffff;">
        <tr>
            <td align="center" style="padding:20px 0;">
                <a href="{{ $mailOrganizerWebsite ?? config('app.frontend_url') }}" target="_blank" style="text-decoration:none;">
                    @if(isset($mailOrganizerLogoUrl))
                        <img
                            src="{{ $mailOrganizerLogoUrl }}"
                            alt="{{ $mailOrganizerName ?? config('app.name') }}"
                            width="120"
                            height="100"
                            border="0"
                            style="display:block; width:120px; max-width:100%; height:auto; border:0; outline:none; text-decoration:none; font-family:sans-serif; font-size:14px; line-height:1.5; color:#333333;"
                        >
                    @else
                        @if($appLogo = config('app.email_logo_url'))
                            <img
                                src="{{ $appLogo }}"
                                alt="{{ config('app.name') }}"
                                width="120"
                                height="100"
                                border="0"
                                class="logo"
                                style="display:block; width:120px; max-width:100%; height:auto; border:0; outline:none; text-decoration:none; font-family:sans-serif; font-size:14px; line-height:1.5; color:#333333;"
                            >
                        @else
                            <img
                                src="{{ config('app.frontend_url') }}/logos/hi-events-stacked-light.png"
                                alt="{{ config('app.name') }}"
                                width="120"
                                height="100"
                                border="0"
                                class="logo"
                                style="display:block; width:120px; max-width:100%; height:auto; border:0; outline:none; text-decoration:none; font-family:sans-serif; font-size:14px; line-height:1.5; color:#333333;"
                            >
                        @endif
                    @endif
                </a>

                {{-- Outlook-spezifischer Fallback (nur für Outlook Desktop sichtbar) --}}
                <!--[if mso]>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="120" style="margin:0 auto;">
                    <tr>
                        <td height="100" style="font-size:0; line-height:0;">
                            &nbsp;
                        </td>
                    </tr>
                </table>
                <![endif]-->
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