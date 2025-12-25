@guest
    <a href="{{ route('filament.patient.auth.login') }}"
       class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">
        <x-heroicon-o-user class="w-5 h-5 text-slate-500" />
        <span>Sign In as Patient</span>
    </a>

    <a href="{{ route('filament.pharmacy.auth.login') }}"
       class="flex items-center gap-3 px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">
        <x-heroicon-o-building-storefront class="w-5 h-5 text-slate-500" />
        <span>Sign In as Pharmacist</span>
    </a>

    <a href="{{ route('register') }}"
       class="flex items-center justify-center gap-2 mt-2 px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700">
        <x-heroicon-o-rocket-launch class="w-5 h-5" />
        <span>Get Started</span>
    </a>
@else
    @php
        $user = Auth::user();
        $panelId = $user->is_pharmacist
            ? 'pharmacy'
            : ($user->is_technician ? 'technician' : 'patient');
    @endphp

    <a href="{{ route("filament.{$panelId}.pages.dashboard") }}"
       class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700">
        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
        <span>My Dashboard</span>
    </a>

    <form method="POST" action="{{ route("filament.{$panelId}.auth.logout") }}" class="mt-2">
        @csrf
        <button type="submit"
                class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded hover:bg-red-100">
            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
            <span>Sign Out</span>
        </button>
    </form>
@endguest
