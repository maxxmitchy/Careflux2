<div
    x-data="{ show: $wire.entangle('show') }"
    x-show="show"
    x-on:close-toast-after-delay.window="
        setTimeout(() => { show = false }, 2500);
    "
    x-cloak
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-5 right-5 z-50 w-full max-w-xs sm:max-w-sm px-4 sm:px-0" {{-- Added px-4 for mobile padding --}}
>
    <div
        class="rounded-lg shadow-md p-3 sm:p-4 flex items-center space-x-3 sm:space-x-4" {{-- Adjusted padding and spacing --}}
        :class="{
            'bg-green-50 border border-green-200': '{{ $type }}' === 'success',
            'bg-red-50 border border-red-200': '{{ $type }}' === 'error',
            'bg-blue-50 border border-blue-200': '{{ $type }}' === 'info',
        }"
    >
        {{-- Icon --}}
        <div class="flex-shrink-0 mt-0.5"> {{-- Added slight top margin for alignment --}}
            <template x-if="'{{ $type }}' === 'success'">
                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </template>
            <template x-if="'{{ $type }}' === 'error'">
                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            </template>
            <template x-if="'{{ $type }}' === 'info'">
                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            </template>
        </div>

        {{-- Message Container --}}
        <div class="flex-1 min-w-0"> {{-- Ensured message takes available space --}}
            <p
                class="text-xs sm:text-sm font-semibold leading-snug" {{-- Font size xs on mobile, sm on desktop, bold text for prominence --}}
                :class="{
                    'text-green-800': '{{ $type }}' === 'success',
                    'text-red-800': '{{ $type }}' === 'error',
                    'text-blue-800': '{{ $type }}' === 'info',
                }"
            >
                {{ $message }}
            </p>
        </div>

        {{-- Close Button --}}
        <div class="ml-2 sm:ml-4 flex-shrink-0 -mr-1.5 -mt-1.5"> {{-- Adjusted margin for better visual spacing --}}
            <button
                @click="show = false"
                class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2" {{-- Smaller padding on button --}}
                :class="{
                    'text-green-600 hover:bg-green-100 focus:ring-green-700 focus:ring-offset-green-50': '{{ $type }}' === 'success',
                    'text-red-600 hover:bg-red-100 focus:ring-red-700 focus:ring-offset-red-50': '{{ $type }}' === 'error',
                    'text-blue-600 hover:bg-blue-100 focus:ring-blue-700 focus:ring-offset-blue-50': '{{ $type }}' === 'info',
                }"
            >
                <span class="sr-only">Close</span>
                <svg class="h-4 w-4 sm:h-5 sm:w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
            </button>
        </div>
    </div>
</div>
