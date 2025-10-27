<?php

namespace Src\Order\Infrastructure\Services;

use App\Jobs\SyncCartToDatabaseJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\DTOs\CartItemDTO;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PrescriptionVerification; // Import DataCollection

class SessionCartService implements CartServiceInterface
{
    private const SESSION_KEY = 'cart.items';

    /**
     * @throws InvalidCartQuantityException
     */
    public function add(array $itemData, string $status): void
    {
        $items = $this->getItemsInternal();
        $existingItem = $items->firstWhere('uniqueId', $itemData['uniqueId']);

        if ($itemData['isPrescription'] && empty($itemData['verificationId'])) {
            throw new InvalidCartQuantityException('This prescription item is missing a valid verification ID.');
        }

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + 1;
            if ($existingItem['isPrescription']) {
                $this->verifyPrescriptionQuantity($existingItem['verificationId'], $newQuantity);
            }
            $this->updateQuantity($existingItem['cartKey'], $newQuantity);
        } else {
            if ($itemData['isPrescription']) {
                $this->verifyPrescriptionQuantity($itemData['verificationId'], 1);
            }
            $items->push([
                ...$itemData,
                'status' => $status, // <-- Add this line
                'cartKey' => now()->timestamp.'-'.Str::random(6),
                'quantity' => 1,
            ]);
            session([self::SESSION_KEY => $items->values()->all()]);

            $this->persist();
        }
    }

    public function applyCoupon(string $cartKey, int $couponId, int $discountAmount): void
    {
        $items = $this->getItemsInternal();
        $key = $items->search(fn ($item) => $item['cartKey'] === $cartKey);

        if ($key !== false) {
            $item = $items[$key];
            $item['applied_coupon_id'] = $couponId;
            // The price of the item itself is now the original price minus the discount
            $item['final_price'] = max(0, $item['price'] - $discountAmount);
            $item['discount_amount'] = $discountAmount;

            $items->put($key, $item);
            session([self::SESSION_KEY => $items->values()->all()]);
        }
    }

    public function clearPendingQuotes(): void
    {
        $items = $this->getItemsInternal()->reject(function ($item) {
            return $item['status'] === 'pending_quote'; // Assuming we add this key
        });
        session([self::SESSION_KEY => $items->values()->all()]);
    }

    public function remove(string $cartKey): void
    {
        $items = $this->getItemsInternal()->reject(fn ($item) => $item['cartKey'] === $cartKey);
        session([self::SESSION_KEY => $items->values()->all()]);

        $this->persist();
    }

    /**
     * @throws InvalidCartQuantityException
     */
    public function updateQuantity(string $cartKey, int $quantity): void
    {
        $items = $this->getItemsInternal();
        $key = $items->search(fn ($item) => $item['cartKey'] === $cartKey);

        if ($key === false || $quantity < 1) {
            return;
        }

        // --- THIS IS THE FIX ---
        // 1. Get a copy of the item.
        $item = $items[$key];

        // 2. Perform validation on the copy.
        if ($item['isPrescription']) {
            $this->verifyPrescriptionQuantity($item['verificationId'], $quantity);
        }

        // 3. Update the quantity on the copy.
        $item['quantity'] = $quantity;

        // 4. Use put() to REPLACE the old item in the collection with our modified copy.
        $items->put($key, $item);
        // --- END OF FIX ---

        session([self::SESSION_KEY => $items->values()->all()]);

        $this->persist();
    }

    /**
     * @throws InvalidCartQuantityException
     */
    private function verifyPrescriptionQuantity(?int $verificationId, int $requestedQuantity): void
    {
        if (is_null($verificationId)) {
            throw new InvalidCartQuantityException('Verification is required for this prescription item.');
        }

        $verification = PrescriptionVerification::find($verificationId);

        if (! $verification || $requestedQuantity > $verification->quantity_allowed) {
            $allowed = $verification?->quantity_allowed ?? 0;
            throw new InvalidCartQuantityException(
                "You are only approved for a quantity of {$allowed} for this prescription."
            );
        }
    }

    /**
     * @return Collection<int, CartItemDTO>
     */
    public function getItems(): Collection
    {
        $dataCollection = CartItemDTO::collect(session(self::SESSION_KEY, []));

        return new Collection($dataCollection);
    }

    public function count(): int
    {
        return $this->getItemsInternal()->count();
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        $this->persist();
    }

    public function getSubtotal(): int
    {
        return $this->getItems()->sum(fn (CartItemDTO $item) => $item->price * $item->quantity);
    }

    public function getItemsInternal(): Collection
    {
        return collect(session(self::SESSION_KEY, []));
    }

    /**
     * Removes an item by its uniqueId, necessary for the quote tracking page.
     */
    public function removeByUniqueId(string $uniqueId): void
    {
        $items = $this->getItemsInternal()->reject(fn ($item) => $item['uniqueId'] === $uniqueId);
        session([self::SESSION_KEY => $items->values()->all()]);
    }

    /**
     * Persists the current session cart to the database if the user is authenticated.
     */
    public function persist(): void
    {
        if (Auth::check()) {
            SyncCartToDatabaseJob::dispatch(Auth::user(), session(self::SESSION_KEY, []));
        }
    }
}
