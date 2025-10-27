<x-filament-widgets::widget>
    <x-filament::card>
        @if($pharmacist)
            <div class="flex items-center gap-4">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($pharmacist->name) }}&background=0284C7&color=fff" alt="{{ $pharmacist->name }}" class="h-16 w-16 rounded-full"/>
                <div>
                    <p class="text-sm text-gray-500">Your Personal Pharmacist</p>
                    <h3 class="text-lg font-bold text-gray-800">{{ $pharmacist->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $pharmacist->pharmacy->name }}</p>
                </div>
            </div>
            <div class="mt-4 border-t pt-4 flex justify-between items-center">
                <span class="text-sm text-gray-500">Public ID: <strong class="font-mono">{{ $pharmacist->public_profile_id }}</strong></span>
                <a href="https://wa.me/{{ $pharmacist->phone }}" target="_blank" class="text-sm font-semibold text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">
                    Chat on WhatsApp
                </a>
            </div>
        @else
            <p class="text-sm text-center text-gray-500 py-4">You have not been assigned a personal pharmacist yet.</p>
        @endif
    </x-filament::card>
</x-filament-widgets::widget>
