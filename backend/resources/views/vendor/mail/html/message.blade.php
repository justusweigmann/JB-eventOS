<x-mail::layout>
    @php /** @var ?string $mailOrganizerLogoUrl */ @endphp
    @php /** @var ?string $mailOrganizerName */ @endphp
    @php /** @var ?string $mailOrganizerWebsite */ @endphp

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