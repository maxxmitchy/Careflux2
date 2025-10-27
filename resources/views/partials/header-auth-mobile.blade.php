@guest
    <a href="{{ route('filament.patient.auth.login') }}" class="block px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">Sign In as Patient</a>
    <a href="{{ route('filament.pharmacy.auth.login') }}" class="block px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">Sign In as Pharmacist</a>
    <a href="{{ route('register') }}" class="block text-center mt-2 px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700">Get Started</a>
@else
    @php
        $user = Auth::user();
        $panelId = $user->is_pharmacist ? 'pharmacy' : ($user->is_technician ? 'technician' : 'patient');
    @endphp
    <a href="{{ route("filament.{$panelId}.pages.dashboard") }}" class="block text-center px-4 py-2 text-sm font-semibold text-white bg-teal-600 rounded hover:bg-teal-700">My Dashboard</a>
    <form method="POST" action="{{ route("filament.{$panelId}.auth.logout") }}" class="mt-2">
        @csrf
        <button type="submit" class="w-full text-center px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded hover:bg-red-100">Sign Out</button>
    </form>
@endguest
