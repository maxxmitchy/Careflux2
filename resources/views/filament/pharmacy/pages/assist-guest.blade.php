<x-filament-panels::page>
    <form wire:submit.prevent="retrieveCart" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Retrieve a Guest's Cart</x-slot>
            <x-slot name="description">Enter the Cart ID provided by the guest on WhatsApp to see the items they need help with.</x-slot>

            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit">
                    Retrieve Cart
                </x-filament::button>
            </div>
        </x-filament::section>
    </form>

    @if($guestCart)
        <x-filament::section class="mt-8">
            <x-slot name="heading">Guest's Items</x-slot>

            <div class="space-y-4">
                @foreach($guestCart as $item)
                    <div class="flex items-center gap-4 p-3 border rounded-lg">
                        <img src="{{ $item['imageUrl'] ?? asset('/images/placeholderimg.jpeg') }}" class="w-12 h-12 rounded-md object-contain">
                        <div class="flex-grow">
                            <p class="text-sm font-semibold">{{ $item['productName'] }}</p>
                            <p class="text-xs text-gray-500">From: {{ $item['sourceName'] }}</p>
                        </div>
                        <p class="text-sm font-bold">₦{{ number_format($item['price'] / 100, 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 border-t pt-4">
                <x-filament::button tag="a" :href="\App\Filament\Pharmacy\Resources\PatientResource::getUrl('create')">
                    Onboard This Patient
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
