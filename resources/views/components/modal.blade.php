@props(['id', 'maxWidth' => '2xl'])

@php
$maxWidthClasses = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: false,
        focusables() {
            // All focusable element types
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() {
            let list = this.focusables()
            let index = list.indexOf(document.activeElement)
            return list[index + 1] || list[0]
        },
        prevFocusable() {
            let list = this.focusables()
            let index = list.indexOf(document.activeElement)
            return list[index - 1] || list.slice(-1)[0]
        }
    }"
    x-on:open-modal.window="$event.detail.id === '{{ $id }}' ? (show = true, $nextTick(() => firstFocusable().focus())) : null"
    x-on:close-modal.window="$event.detail.id === '{{ $id }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Panel -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full mx-auto bg-white rounded-xl shadow-xl transform transition-all {{ $maxWidthClasses }}"
    >
        {{-- Optional close button in the corner --}}
        <button @click="show = false" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-gray-600">
            <x-heroicon-o-x-mark class="h-6 w-6" />
        </button>

        {{ $slot }}
    </div>
</div>
