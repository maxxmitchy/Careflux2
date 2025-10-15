<div>
    <h3 class="text-lg font-semibold mb-4">Assigned Products</h3>
    <ul class="list-disc list-inside space-y-2 text-sm text-gray-700">
        @forelse($subjects as $product)
            <li>{{ $product->name }}</li>
        @empty
            <li>No products are assigned to this task.</li>
        @endforelse
    </ul>
</div>
