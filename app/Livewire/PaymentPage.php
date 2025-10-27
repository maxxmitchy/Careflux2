<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Order\Application\Actions\VerifyTransactionAction;
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

        $checkoutData = [
            'firstName' => $nameParts[0],
            'lastName' => $nameParts[1] ?? '',
            'email' => $user->email,
            'currency' => 'NGN',
            'amount' => $this->transaction->amount / 100, // Transactpay expects Naira
            'mobile' => $user->phone ?? '',
            'reference' => $attempt->reference, // Use the unique attempt reference
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
