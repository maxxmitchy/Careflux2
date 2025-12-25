@guest
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button @click="open = !open"
            class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition-colors flex items-center gap-1">
            <span>Sign In</span>
            <x-heroicon-s-chevron-down class="h-3 w-3" />
        </button>
        <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border">
            <a href="{{ route('filament.patient.auth.login') }}"
                class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Patient Portal</a>
            <a href="{{ route('filament.pharmacy.auth.login') }}"
                class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Pharmacist Portal</a>
            <a href="{{ route('filament.technician.auth.login') }}"
                class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">Technician Portal</a>
        </div>
    </div>
    <a href="{{ route('register') }}"
        class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">Get
        Started</a>
@else
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button @click="open = !open"
            class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-emerald-600">
            <span>{{ Auth::user()->name }}</span>
            <x-heroicon-s-chevron-down class="h-3 w-3" />
        </button>
        <div x-show="open" x-transition x-cloak
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border">
            @php
                $user = Auth::user();
                $panelId = $user->is_pharmacist ? 'pharmacy' : ($user->is_technician ? 'technician' : 'patient');
            @endphp
            <a href="{{ route("filament.{$panelId}.pages.dashboard") }}"
                class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Dashboard</a>
            {{-- <a href="{{ route("filament.{$panelId}.pages.profile") }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-100">My Profile</a>            <div class="border-t my-1"></div> --}}
            <form method="POST" action="{{ route("filament.{$panelId}.auth.logout") }}">
                @csrf
                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Sign
                    Out</button>
            </form>
        </div>
    </div>
@endguest
