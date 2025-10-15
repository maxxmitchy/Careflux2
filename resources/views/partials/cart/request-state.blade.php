<div class="lg:grid lg:grid-cols-3 lg:gap-8">
    <!-- Left Column: Items to Confirm -->
    <div class="lg:col-span-2">
        <h3 class="text-base font-semibold text-gray-900">Items Awaiting Confirmation</h3>
        <p class="mt-1 text-xs text-gray-600">These items require our team to manually verify the price and availability with the source pharmacy. Once verified, we will notify you.</p>
        <div class="mt-6 space-y-4">
            @foreach($this->pendingQuoteItems() as $item)
                <x-cart.quote-item-card :item="$item" />
            @endforeach
        </div>
    </div>

    <!-- Right Column: Contact Form -->
    <aside class="lg:col-span-1 mt-8 lg:mt-0">
        <div class="bg-white rounded shadow-sm border p-4 sticky top-24">
            <h3 class="text-base font-semibold">Confirm Your Request</h3>
            <p class="mt-1 text-xs text-gray-500">Confirm your contact details so we can send you the finalized quote and payment link.</p>
            <form wire:submit.prevent="submitQuoteRequest" class="mt-4 space-y-4">
                <div>
                    <label for="quote_name" class="block text-xs font-medium text-gray-700">Full Name</label>
                    <input id="quote_name" wire:model.lazy="quote_name" type="text" required class="mt-1 w-full p-3 border text-sm border-gray-300 rounded">
                    @error('quote_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="quote_phone" class="block text-xs font-medium text-gray-700">Phone / WhatsApp</label>
                    <input id="quote_phone" wire:model.lazy="quote_phone" type="tel" required class="mt-1 w-full p-3 border text-sm border-gray-300 rounded">
                    @error('quote_phone') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="quote_email" class="block text-xs font-medium text-gray-700">Email Address</label>
                    <input id="quote_email" wire:model.lazy="quote_email" type="email" required class="mt-1 w-full p-3 border text-sm border-gray-300 rounded">
                    @error('quote_email') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="pt-2">
                    <label for="quote_consent" class="flex items-start gap-3">
                        <input id="quote_consent" wire:model.lazy="quote_consent" type="checkbox" class="mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600">
                        <span class="text-xs text-gray-600">I understand this is a request, not a purchase, and I consent to being contacted.</span>
                    </label>
                    @error('quote_consent') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full bg-emerald-600 text-white py-2.5 rounded text-sm font-semibold hover:bg-emerald-700 flex items-center justify-center gap-2"
                        wire:loading.attr="disabled"
                        wire:target="submitQuoteRequest"
                    >
                        <span wire:loading wire:target="submitQuoteRequest">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="submitQuoteRequest">
                            Submit Request
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </aside>
</div>
