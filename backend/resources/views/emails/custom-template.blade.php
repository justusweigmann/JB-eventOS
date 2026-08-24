{{-- Custom Liquid Template Wrapper --}}
<x-mail::message>
    :logo-url="$mailOrganizerLogoUrl ?? config('app.organizer_logo_url')"
    :logo-link-url="$mailOrganizerWebsite ?? config('app.frontend_url')"
    :logo-alt-text="$mailOrganizerName ?? config('app.name')"
>
    {!! $renderedBody !!}

    @if(isset($renderedCta))
        <x-mail::button :url="$renderedCta['url']">
            {{ $renderedCta['label'] }}
        </x-mail::button>
    @endif

    {!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>