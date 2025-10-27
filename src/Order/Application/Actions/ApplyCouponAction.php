<?php

namespace Src\Order\Application\Actions;

use App\Models\Coupon;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Shared\Domain\Models\User;

class ApplyCouponAction
{
    public function execute(User $user, CartServiceInterface $cartService, int $couponId, string $cartKey): bool
    {
        $coupon = Coupon::find($couponId);
        $item = $cartService->getItemsInternal()->firstWhere('cartKey', $cartKey);

        if (
            ! $coupon || // Coupon doesn't exist
            $coupon->user_id !== $user->id || // Doesn't belong to this user
            $coupon->redeemed_at !== null || // Already used
            $coupon->expires_at->isPast() || // Expired
            $item['uniqueId'] !== $coupon->productable->getMorphClass().'::'.$coupon->productable->id // Doesn't match the product
        ) {
            return false;
        }

        $cartService->applyCoupon($cartKey, $couponId, $coupon->discount_amount);

        return true;
    }
}
