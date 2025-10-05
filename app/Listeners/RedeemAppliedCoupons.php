<?php

namespace App\Listeners;

use App\Events\TransactionCompleted;
use App\Models\Coupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class RedeemAppliedCoupons implements ShouldQueue
{
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;
        $invoiceIds = $transaction->metadata['invoice_ids'] ?? [];

        if (empty($invoiceIds)) {
            return;
        }

        $couponIdsToRedeem = DB::table('invoice_items')
            ->whereIn('invoice_id', $invoiceIds)
            ->whereNotNull('coupon_id')
            ->pluck('coupon_id')
            ->unique()
            ->all();

        if (! empty($couponIdsToRedeem)) {
            Coupon::whereIn('id', $couponIdsToRedeem)
                ->whereNull('redeemed_at') // Failsafe to prevent double redemption
                ->update(['redeemed_at' => now()]);
        }
    }
}
