<x-mail::layout>
    @php /** @var ?string $mailOrganizerLogoUrl */ @endphp
    @php /** @var ?string $mailOrganizerName */ @endphp
    @php /** @var ?string $mailOrganizerWebsite */ @endphp

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

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ config('app.name') }}
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>