<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Contracts\PlaceOrderActionInterface;
use Src\Order\Domain\DTOs\CartItemDTO;
use Src\Subscription\Application\Actions\LogTransactionAction;
use Src\User\Domain\DTOs\CustomerDataDTO;
use Src\User\Domain\DTOs\ShippingDataDTO;

#[Layout('components.layouts.guest')]
class CheckoutPage extends Component
{
    // Store raw items as array for reactivity
    public array $cartItemsArray = [];

    public int $subtotal = 0;

    public int $deliveryFee = 50000; // ₦500 in kobo

    // Form Properties
    public string $full_name = '';

    public string $email = '';

    public string $phone = '';

    public string $location_area = '';

    public bool $shipToDifferentAddress = false;

    public string $shipping_full_name = '';

    public string $shipping_phone = '';

    public string $shipping_location_area = '';

    // UI State
    public bool $mobileFormExpanded = false;

    public function mount(CartServiceInterface $cartService)
    {
        // Use array version from cart service
        $this->cartItemsArray = $cartService->getItemsInternal()->all();

        if (empty($this->cartItemsArray)) {
            return redirect()->route('cart');
        }

        // Calculate subtotal manually
        $this->subtotal = collect($this->cartItemsArray)
            ->sum(fn ($item) => $item['price'] * $item['quantity']);

        if ($user = Auth::user()) {
            $this->full_name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->location_area = $user->patientProfile?->location_area ?? '';

            // Expand if essential info is missing
            if (empty($this->phone) || empty($this->location_area)) {
                $this->mobileFormExpanded = true;
            }
        } else {
            // Guests must fill form
            $this->mobileFormExpanded = true;
            session(['url.intended' => route('checkout')]);
        }
    }

    #[Computed]
    public function cartItems(): Collection
    {
        $dataCollection = CartItemDTO::collect($this->cartItemsArray);

        // Explicitly cast to a new base Collection to satisfy the type hint
        return new Collection($dataCollection);
    }

    protected function rules(): array
    {
        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:10'],
            'location_area' => ['required', 'string', 'max:255'],
        ];

        if ($this->shipToDifferentAddress) {
            $rules['shipping_full_name'] = ['required', 'string', 'max:255'];
            $rules['shipping_phone'] = ['required', 'string', 'min:10'];
            $rules['shipping_location_area'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function placeOrder(
        PlaceOrderActionInterface $placeOrderAction,
        CartServiceInterface $cartService,
        LogTransactionAction $logTransactionAction
    ) {
        $this->validate();

        $customerData = CustomerDataDTO::from($this->only(['full_name', 'email', 'phone', 'location_area']));
        $shippingData = $this->shipToDifferentAddress
            ? ShippingDataDTO::from(['full_name' => $this->shipping_full_name, 'phone' => $this->shipping_phone, 'location_area' => $this->shipping_location_area])
            : null;

        $invoices = $placeOrderAction->execute($customerData, $shippingData, $this->cartItems());

        if ($invoices->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'There was a problem creating your order.');

            return;
        }

        $cartService->clear();
        $this->dispatch('cart-updated');

        // --- THIS IS THE DEFINITIVE FIX ---
        $totalAmount = $invoices->sum('total');
        $actingUser = Auth::user() ?? $invoices->first()->patient->user;
        $customer = $invoices->first()->pharmacy; // The pharmacy is the customer in this context

        // 3. Use the robust, architecturally correct Action to create the transaction
        $transaction = $logTransactionAction->execute(
            actingUser: $actingUser,
            customer: $customer,
            transactionable: $actingUser, // Or another relevant model if needed
            amountInKobo: $totalAmount,
            gateway: 'transactpay'
        );

        // Add the invoice IDs to the metadata after creation
        $transaction->update(['metadata' => ['invoice_ids' => $invoices->pluck('id')->all()]]);
        // --- END OF FIX ---

        return redirect()->route('payment.page', ['reference' => $transaction->reference]);
    }

    public function getTotalProperty(): int
    {
        return $this->subtotal + $this->deliveryFee;
    }

    public function render()
    {
        return view('livewire.checkout-page');
    }
}
