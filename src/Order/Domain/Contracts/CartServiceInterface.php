<?php

namespace Src\Order\Domain\Contracts;

use Illuminate\Support\Collection;
use Src\Order\Domain\DTOs\CartItemDTO;

interface CartServiceInterface
{
    public function add(array $itemData, string $status): void;

    public function remove(string $cartKey): void;

    public function updateQuantity(string $cartKey, int $quantity): void;

    /** @return Collection<int, CartItemDTO> */
    public function getItems(): Collection;

    public function count(): int;

    public function clear(): void;

    public function getSubtotal(): int;

    public function getItemsInternal(): Collection;

    public function clearPendingQuotes(): void;

    public function applyCoupon(string $cartKey, int $couponId, int $discountAmount): void;

    public function removeByUniqueId(string $uniqueId): void;
}
