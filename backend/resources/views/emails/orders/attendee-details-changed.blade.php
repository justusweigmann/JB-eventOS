@php /** @var string $ticketTitle */ @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\OrganizerDomainObject $organizer */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var array $changedFields */ @endphp

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
# {{ __('Ticket Details Changed') }}

{{ __('The details on your ticket for **:eventName** have been updated.', ['eventName' => $event->getTitle()]) }}

**{{ __('Ticket') }}**: {{ $ticketTitle }}

## {{ __('What Changed') }}

@foreach($changedFields as $field => $change)
- **{{ $field }}**: {{ $change['old'] }} → {{ $change['new'] }}
@endforeach

{{ __('If you did not make this change, please contact the event organizer immediately.') }}

---

{{ __('Event Organizer: :organizerName', ['organizerName' => $organizer->getName()]) }}

@if($eventSettings->getSupportEmail())
{{ __('Contact: :email', ['email' => $eventSettings->getSupportEmail()]) }}
@endif

{{ __('Thanks,') }}<br>
{{ $organizer->getName() }}
</x-mail::message>
