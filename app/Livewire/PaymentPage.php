<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Order\Application\Actions\VerifyTransactionAction;
use Src\Order\Domain\Models\InvoiceItem;
use Src\Order\Domain\Models\Transaction; // We will create this

#[Layout('components.layouts.guest')]
class PaymentPage extends Component
{
    public Transaction $transaction;

    public function mount(string $reference)
    {
        $this->transaction = Transaction::where('reference', $reference)
            ->where('status', 'pending')
            ->firstOrFail();

        if (Auth::check() && Auth::id() !== $this->transaction->user_id) {
            abort(403);
        }
    }

    public function triggerPayment()
    {
        $attempt = $this->transaction->paymentAttempts()->create([
            'reference' => (string) Str::uuid(),
            'status' => 'pending',
        ]);

        $user = $this->transaction->user;
        $nameParts = explode(' ', trim($user->name), 2);

        // --- GTM EVENT IMPLEMENTATION ---

        // 2. Get the invoice IDs from the transaction metadata.
        $invoiceIds = $this->transaction->metadata['invoice_ids'] ?? [];

        // 3. Fetch all related invoice items in a single, efficient query.
        $invoiceItems = InvoiceItem::query()
            ->whereIn('invoice_id', $invoiceIds)
            ->with('invoice.pharmacy') // Eager-load the pharmacy for the 'brand' field
            ->get();

        // 4. Transform the Eloquent models into the GTM 'items' array format.
        $gtmItems = $invoiceItems->map(function (InvoiceItem $item) {
            return [
                'item_id' => $item->invoice->invoice_number.'-'.$item->id, // A unique ID for the line item
                'item_name' => $item->description,
                'item_brand' => $item->invoice->pharmacy->name,
                'price' => $item->price / 100, // Convert from kobo to Naira
                'quantity' => $item->quantity,
            ];
        })->all();

        // 5. Dispatch the complete event to the data layer.
        $this->dispatch('gtm-event', [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'value' => $this->transaction->amount / 100, // Naira
                'currency' => 'NGN',
                'transaction_id' => $this->transaction->reference,
                'items' => $gtmItems, // Use the populated items array
            ],
        ]);

        // --- END GTM EVENT IMPLEMENTATION ---

        $checkoutData = [
            'firstName' => $nameParts[0],
            'lastName' => $nameParts[1] ?? '',
            'email' => $user->email,
            'currency' => 'NGN',
            'amount' => $this->transaction->amount / 100,
            'mobile' => $user->phone ?? '',
            'reference' => $attempt->reference,
            'description' => 'Payment for Careflux Order',
            'apiKey' => config('services.transactpay.public_key'),
            'encryptionKey' => config('services.transactpay.encryption_key'),
        ];

        $this->dispatch('start-checkout', data: $checkoutData);
    }

    public function handlePaymentCallback(string $attemptReference, VerifyTransactionAction $verifyAction)
    {
        if ($verifyAction->execute($attemptReference)) {
            return redirect()->route('order.complete', ['reference' => $this->transaction->reference]);
        }

        $this->dispatch('toast', type: 'error', message: 'Payment verification failed. Please try again or contact support.');
    }

    public function render()
    {
        return view('livewire.payment-page');
    }
}
