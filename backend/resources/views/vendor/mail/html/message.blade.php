<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        @php
            $logoUrl = !empty($mailOrganizerLogoUrl ?? null)
                ? $mailOrganizerLogoUrl
                : (
                    config('app.email_logo_url')
                    ?: rtrim((string) config('app.frontend_url'), '/')
                        . '/logos/hi-events-stacked-light.png'
                );

            $logoLinkUrl = !empty($mailOrganizerWebsite ?? null)
                ? $mailOrganizerWebsite
                : (
                    config('app.email_logo_link_url')
                    ?: config('app.frontend_url')
                );

            $logoAltText = !empty($mailOrganizerName ?? null)
                ? $mailOrganizerName
                : config('app.name');
        @endphp

        <x-mail::header :url="$logoLinkUrl">
            <img
                src="{{ $logoUrl }}"
                class="logo"
                alt="{{ $logoAltText }}"
                style="
                    display: block;
                    width: auto;
                    max-width: 100%;
                    height: auto;
                    max-height: 250px;
                    object-fit: contain;
                    margin: 0 auto;
                "
            >
        </x-mail::header>
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
            @if($appEmailFooter = config('app.email_footer_text'))
                {{ $appEmailFooter }}
            @else
                © {{ date('Y') }} {{ config('app.name') }}
                |
                Powered by
                <a
                    title="Manage events and sell tickets online with Hi.Events"
                    href="https://hi.events?utm_source=app-email-footer"
                >
                    Hi.Events
                </a>
            @endif
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>