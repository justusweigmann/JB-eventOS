@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp


@php /** @see \HiEvents\Mail\Order\OrderCancelled */ @endphp

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


{{ __('Your order for') }} <b>{{$event->getTitle()}}</b> {{ __('has been cancelled.') }}
<br>
<br>
{{ __('Order #:') }} <b>{{$order->getPublicId()}}</b>
<br>
<br>
{{ __('If you have any questions or need assistance, please respond to this email.') }}
<br><br>
{{ __('Thank you') }},<br>
{{ $organizer->getName() ?: config('app.name') }}


{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>