<x-mail::layout>
    {{-- Header --}}
        @if(isset($mailOrganizerLogoUrl))
        <x-slot:header>
            <table class="header" align="center" width="600" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center" style="padding: 24px 32px;">
                        <a href="{{ $mailOrganizerWebsite ?? config('app.frontend_url') }}" target="_blank">
                            <img src="{{ $mailOrganizerLogoUrl }}" 
                                 alt="{{ $mailOrganizerName ?? config('app.name') }}"
                                 style="max-height: 150px; display: block;">
                        </a>
                    </td>
                </tr>
            </table>
        </x-slot:header>
    @endif

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