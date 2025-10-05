<div class="min-h-screen my-32">
    <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-lg border">
        <h2 class="text-xl font-bold text-center">One Last Step</h2>
        <p class="text-center text-xs text-gray-500 mt-2">Please provide your phone number to complete your profile.</p>
        <form wire:submit.prevent="save" class="mt-6 space-y-4">
            <div>
                <label for="phone" class="block text-xs font-medium text-gray-700">Phone Number</label>
                <input id="phone" wire:model="phone" type="tel" required class="mt-1 w-full p-2 text-sm border-gray-300 rounded-lg">
                @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full bg-emerald-600 text-white py-2.5 rounded-lg text-sm font-semibold">Complete Profile</button>
        </form>
    </div>
</div>
