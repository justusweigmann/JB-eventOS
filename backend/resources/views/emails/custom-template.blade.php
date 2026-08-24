{{-- Custom Liquid Template Wrapper --}}

@if(isset($mailOrganizerLogoUrl))
    <img src="{{ $mailOrganizerLogoUrl }}" alt="Organizer Logo" style="max-height:150px; align:center; display:block; margin-left:auto; margin-right:auto; margin-top:2rem; margin-bottom:1rem;">
@else
    LOGO FEHLT IM CUSTOM TEMPLATE
@endif

<x-mail::message>
{!! $renderedBody !!}

@if(isset($renderedCta))
<x-mail::button :url="$renderedCta['url']">
    {{ $renderedCta['label'] }}
</x-mail::button>
@endif

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>