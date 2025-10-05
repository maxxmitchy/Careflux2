<div class="grid grid-cols-2 lg:grid-cols-4 gap-6"> {{-- Adjust padding as needed --}}
    @for ($i = 0; $i < 8; $i++) {{-- Display 8 skeleton items --}}
        <div class="w-44 group relative overflow-hidden rounded-lg bg-white p-4 space-y-4">
            {{-- Image Placeholder --}}
            <div class="h-36 w-full bg-slate-50 rounded-lg animate-shimmer"></div>

            {{-- Title Placeholder --}}
            <div class="h-5 w-3/4 rounded animate-shimmer"></div>

            {{-- Text Line Placeholders --}}
            <div class="space-y-2">
                <div class="h-3 w-full bg-stone-100 rounded animate-shimmer"></div>
                <div class="h-3 w-5/6 bg-stone-100 rounded animate-shimmer"></div>
                <div class="h-3 w-1/2 bg-stone-100 rounded animate-shimmer"></div>
            </div>

            {{-- Button/Action Placeholder --}}
            <div class="h-8 w-1/3 bg-gray-100 rounded-full ml-auto animate-shimmer"></div>
        </div>
    @endfor
</div>
