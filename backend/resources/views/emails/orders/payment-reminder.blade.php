@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var ?string $mailOrganizerLogoUrl */ @endphp
@php /** @var ?string $mailOrganizerName */ @endphp
@php /** @var ?string $mailOrganizerWebsite */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var string $paymentExpiryDate */ @endphp
@php /** @var int $hoursUntilExpiry */ @endphp

<x-mail::message>
{{ __('Hello') }},

@if($hoursUntilExpiry >= 72)
    <p>
    {{ __('This is a friendly reminder that your order for :eventTitle is pending payment.', ['eventTitle' => $event->getTitle()]) }}
    </p>

    <p>
    {{ __('To complete your purchase, please proceed with the payment before') }} <strong>{{ $paymentExpiryDate }}</strong>. {{ __('After this time, your order will expire and the reserved tickets will be released.') }}
    </p>
@else
    <p>
    {{ __('URGENT: Your order for :eventTitle will expire soon!', ['eventTitle' => $event->getTitle()]) }}
    </p>

    <p>
    {{ __('Your payment is due within the next 24 hours. To secure your tickets, please complete the payment before') }} <strong>{{ $paymentExpiryDate }}</strong>.
    </p>

    <p>
    {{ __('If payment is not received by this time, your order will be automatically cancelled and the tickets will be released to other customers.') }}
    </p>
@endif

<x-mail::button :url="$order->getCheckoutUrl()">
{{ __('Complete Payment Now') }}
</x-mail::button>

<p>
{{ __('If you have already completed the payment, you can ignore this email.') }}
</p>

<p>
{{ __('If you have any questions or need assistance, feel free to reach us at') }} <a href="mailto:{{$organizer->getEmail()}}">{{$organizer->getEmail()}}</a>.
</p>

{{ __('Best regards') }},<br>
{{ $organizer->getName() ?: config('app.name') }}

{!! $eventSettings->getGetEmailFooterHtml() !!}
</x-mail::message>