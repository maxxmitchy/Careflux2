<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    {{-- Vite will inject the compiled CSS directly into the HTML head --}}
    @vite(['resources/css/app.css'])
    <style>
        /* Additional styles for print-perfect layout if needed */
        body { -webkit-print-color-adjust: exact; }
    </style>
</head>
<body class="bg-white font-sans p-8">
    {{-- Header --}}
    <header class="flex justify-between items-start mb-12">
        <div>
            <img src="{{ public_path('images/logo.jpg') }}" alt="Careflux Logo" style="height: 40px;">
            <p class="text-xs text-gray-500 mt-2">Careflux Pharmacy Partner</p>
        </div>
        <div class="text-right">
            <h1 class="text-3xl font-bold text-gray-800">INVOICE</h1>
            <p class="text-sm text-gray-500">{{ $invoice->invoice_number }}</p>
        </div>
    </header>

    {{-- Pharmacy and Patient Details --}}
    <section class="grid grid-cols-2 gap-8 mb-10">
        <div>
            <h2 class="text-xs font-bold uppercase text-gray-500 tracking-wider">From</h2>
            <p class="font-semibold text-gray-800">{{ $invoice->pharmacy->name }}</p>
            <p class="text-xs text-gray-600">{{ $invoice->pharmacy->address }}</p>
            <p class="text-xs text-gray-600">{{ $invoice->pharmacy->phone }}</p>
        </div>
        <div class="text-right">
            <h2 class="text-xs font-bold uppercase text-gray-500 tracking-wider">Bill To</h2>
            <p class="font-semibold text-gray-800">{{ $invoice->patient->full_name }}</p>
            <p class="text-xs text-gray-600">{{ $invoice->shipping_location_area ?? $invoice->patient->location_area }}</p>
            <p class="text-xs text-gray-600">{{ $invoice->shipping_phone ?? $invoice->patient->phone }}</p>
        </div>
    </section>

    <!-- Items Table -->
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-100 text-xs uppercase">
                <th class="p-3 font-semibold">Description</th>
                <th class="p-3 text-center font-semibold">Qty</th>
                <th class="p-3 text-right font-semibold">Unit Price</th>
                <th class="p-3 text-right font-semibold">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr class="border-b text-sm">
                    <td class="p-3">{{ $item->description }}</td>
                    <td class="p-3 text-center">{{ $item->quantity }}</td>
                    <td class="p-3 text-right">₦{{ number_format($item->price / 100, 2) }}</td>
                    <td class="p-3 text-right font-semibold">₦{{ number_format($item->total / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Section -->
    <section class="mt-8 flex justify-end">
        <div class="w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium text-gray-800">₦{{ number_format($invoice->subtotal / 100, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Delivery Fee:</span>
                <span class="font-medium text-gray-800">₦{{ number_format($invoice->delivery_fee / 100, 2) }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t font-bold text-base">
                <span class="text-gray-900">Total Due:</span>
                <span class="text-emerald-600">₦{{ number_format($invoice->total / 100, 2) }}</span>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-16 pt-6 border-t text-center text-xs text-gray-500">
        <p>Thank you for choosing Careflux. For any inquiries, please contact your personal pharmacist.</p>
        <p>Careflux | support@careflux.com</p>
    </footer>
</body>
</html>
