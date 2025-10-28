<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Location\Domain\Models\City;
use Src\Location\Domain\Models\State;
use Src\Order\Application\Actions\CreateTransactionFromInvoicesAction;
use Src\Order\Application\Services\DeliveryCostService;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Contracts\PlaceOrderActionInterface;
use Src\Order\Domain\DTOs\CartItemDTO;
use Src\User\Domain\DTOs\CustomerDataDTO;
use Src\User\Domain\DTOs\ShippingDataDTO;

#[Layout('components.layouts.guest')]
class CheckoutPage extends Component
{
    // Store raw items as array for reactivity
    public array $cartItemsArray = [];

    public int $subtotal = 0;

    public int $deliveryFee = 0;

    // Form Properties
    public string $full_name = '';

    public string $email = '';

    public string $phone = '';

    public ?string $state_id = '';

    public ?string $city_id = '';

    public ?string $shipping_state_id = '';

    public ?string $shipping_city_id = '';

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

        $this->recalculateDeliveryFee();
    }

    /**
     * This is a "hook" method that Livewire calls automatically
     * whenever a public property is updated.
     */
    public function updated($property): void
    {
        // When a state is changed, reset the city
        if ($property === 'state_id') {
            $this->city_id = '';
        }
        if ($property === 'shipping_state_id') {
            $this->shipping_city_id = '';
        }

        // Recalculate delivery fee whenever any part of the address changes
        if (in_array($property, ['city_id', 'shipping_city_id', 'shipToDifferentAddress'])) {
            $this->recalculateDeliveryFee();
        }
    }

    public function recalculateDeliveryFee()
    {
        $deliveryService = app(DeliveryCostService::class);
        $destination = $this->getDestinationString();

        $this->deliveryFee = $deliveryService->calculateForCart(collect($this->cartItemsArray), $destination);
    }

    private function getDestinationString(): string
    {
        $cityId = $this->shipToDifferentAddress ? $this->shipping_city_id : $this->city_id;
        $stateId = $this->shipToDifferentAddress ? $this->shipping_state_id : $this->state_id;

        if (! $cityId || ! $stateId) {
            return '';
        }

        $city = City::find($cityId);
        $state = State::find($stateId);

        return $city && $state ? "{$city->name}, {$state->name}" : '';
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
            'full_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $trimmed = trim($value);

                    // Must be at least 3 characters
                    if (strlen($trimmed) < 3) {
                        return $fail('The full name must be at least 3 characters.');
                    }

                    // Split by one or more spaces
                    $parts = preg_split('/\s+/', $trimmed);

                    // If user only entered one word, duplicate it
                    if (count($parts) === 1) {
                        $this->full_name = "{$parts[0]} {$parts[0]}";
                    } else {
                        // Normalize spacing in multi-word names
                        $this->full_name = implode(' ', $parts);
                    }
                },
            ],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:10'],
            'location_area' => ['required', 'string', 'max:255'],
        ];

        if ($this->shipToDifferentAddress) {
            $rules['shipping_full_name'] = [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $trimmed = trim($value);

                    if (strlen($trimmed) < 3) {
                        return $fail('The shipping name must be at least 3 characters.');
                    }

                    $parts = preg_split('/\s+/', $trimmed);

                    if (count($parts) === 1) {
                        $this->shipping_full_name = "{$parts[0]} {$parts[0]}";
                    } else {
                        $this->shipping_full_name = implode(' ', $parts);
                    }
                },
            ];
            $rules['shipping_phone'] = ['required', 'string', 'min:10'];
            $rules['shipping_location_area'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function placeOrder(
        PlaceOrderActionInterface $placeOrderAction,
        CartServiceInterface $cartService,
        CreateTransactionFromInvoicesAction $createTransactionAction // <-- Inject the new action
    ) {
        $this->location_area = $this->getDestinationString();

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

        $transaction = $createTransactionAction->execute($invoices);

        return redirect()->route('payment.page', ['reference' => $transaction->reference]);
    }

    #[Computed]
    public function states(): Collection
    {
        return State::where('country_id', 1)->get();
    }

    #[Computed]
    public function cities(): Collection
    {
        if (! $this->state_id) {
            return collect();
        }

        return City::where('state_id', $this->state_id)->get();
    }

    #[Computed]
    public function shippingCities(): Collection
    {
        if (! $this->shipping_state_id) {
            return collect();
        }

        return City::where('state_id', $this->shipping_state_id)->get();
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
