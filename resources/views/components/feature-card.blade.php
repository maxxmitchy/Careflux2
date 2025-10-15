@props([
    'icon' => null,
    'title',
    'description',
])

<div {{ $attributes->merge([
    'class' => 'group flex flex-col items-center text-center bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 hover:border-emerald-200'
]) }}>
    @if($icon)
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition-colors duration-300 mb-4">
            <x-dynamic-component :component="$icon" class="w-6 h-6" />
        </div>
    @endif

    <h3 class="text-base font-semibold text-gray-900 mb-2 leading-tight">
        {{ $title }}
    </h3>

    <p class="text-sm text-gray-600 leading-relaxed">
        {{ $description }}
    </p>
</div>
