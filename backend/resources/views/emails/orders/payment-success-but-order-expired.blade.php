@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp

@php /** @see \HiEvents\Mail\Order\PaymentSuccessButOrderExpiredMail */ @endphp

@if(isset($mailOrganizerLogoUrl))
    <img
        src="{{ $mailOrganizerLogoUrl }}"
        alt="Organizer Logo"
        style="max-height: 150px; center; display: block; margin-left: auto; margin-right: auto; margin-top: 2rem; margin-bottom: 1rem;"
    >
@else
    LOGO FEHLT IM MAIL-VIEW
@endif

<x-mail::message>
{{ __('Hello') }},

<p>
{{ __('Your recent order for :eventTitle was not successful. The order expired while you were completing the payment. We have issued a refund for the order.', ['eventTitle' => $event->getTitle()]) }}
</p>

<p>
{{ __('We apologize for the inconvenience. If you have any questions or need assistance, feel free to reach us at') }} <a href="mailto:{{$organizer->getEmail()}}">{{$organizer->getEmail()}}</a>.
</p>

<x-mail::button :url="$event->getEventUrl()">
{{ __('View Event Page') }}
</x-mail::button>

{{ __('Best regards') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>
