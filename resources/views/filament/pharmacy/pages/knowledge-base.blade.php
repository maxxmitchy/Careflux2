<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left Sidebar: Categories & Search -->
        <aside class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div>
                    <label for="search" class="sr-only">Search</label>
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search templates..." class="p-3 border w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="space-y-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase">Categories</h3>
                    <button wire:click="$set('selectedCategory', null)" @class(['w-full text-left text-sm p-2 rounded-lg', 'bg-emerald-100 text-emerald-800 font-semibold' => is_null($selectedCategory), 'hover:bg-gray-100' => !is_null($selectedCategory)])>
                        All Templates
                    </button>
                    @foreach($categories as $category)
                        <button wire:click="$set('selectedCategory', {{ $category->id }})" @class(['w-full text-left text-sm p-2 rounded-lg', 'bg-emerald-100 text-emerald-800 font-semibold' => $selectedCategory === $category->id, 'hover:bg-gray-100' => $selectedCategory !== $category->id])>
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- Main Content: Articles -->
        <main class="lg:col-span-3">
            <div class="space-y-4">
                @forelse($this->articles as $article)
                    <div wire:key="{{ $article->id }}"
                        x-data="{ copied: false }"
                        class="bg-white p-4 rounded-lg border border-gray-300 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">{{ $article->title }}</h3>
                                <p class="text-xs text-gray-500">{{ $article->category->name }}</p>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <button
                                    x-on:click="
                                        navigator.clipboard.writeText($refs.content.innerText);
                                        copied = true;
                                        setTimeout(() => copied = false, 2000);
                                    "
                                    class="flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-md"
                                    :class="copied ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                >
                                    <x-heroicon-s-clipboard x-show="!copied" class="h-4 w-4"/>
                                    <x-heroicon-s-check x-show="copied" x-cloak class="h-4 w-4"/>
                                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Make sure this x-ref is inside the same x-data scope -->
                        <div x-ref="content" class="mt-4 prose prose-sm max-w-none text-gray-600 p-3 bg-gray-50 rounded-md">
                            {!! Str::markdown($article->content) !!}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <x-heroicon-o-document-magnifying-glass class="mx-auto h-12 w-12 text-gray-400"/>
                        <p class="mt-2 text-sm font-medium text-gray-600">No templates found.</p>
                    </div>
                @endforelse

                @if ($this->articles->hasPages())
                    <div class="mt-10 flex justify-center">
                        <div class="inline-flex flex-wrap items-center justify-center gap-2">
                            {{-- Previous Page --}}
                            @if ($this->articles->onFirstPage())
                                <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                                    ← Prev
                                </span>
                            @else
                                <button
                                    wire:click="previousPage"
                                    class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-emerald-100 rounded-md transition"
                                >
                                    ← Prev
                                </button>
                            @endif

                            {{-- Page Numbers --}}
                            @foreach ($this->articles->links()->elements[0] ?? [] as $page => $url)
                                @if (is_string($page))
                                    <span class="px-3 py-1.5 text-sm text-gray-400">...</span>
                                @else
                                    <button
                                        wire:click="gotoPage({{ $page }})"
                                        class="px-3 py-1.5 text-sm rounded-md transition
                                        {{ $page === $this->articles->currentPage() ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-emerald-100' }}">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach

                            {{-- Next Page --}}
                            @if ($this->articles->hasMorePages())
                                <button
                                    wire:click="nextPage"
                                    class="px-3 py-1.5 text-sm text-gray-700 bg-gray-100 hover:bg-emerald-100 rounded-md transition"
                                >
                                    Next →
                                </button>
                            @else
                                <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                                    Next →
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>
</x-filament-panels::page>
