<?php

namespace App\Listeners;

use App\Jobs\SyncCartToDatabaseJob;
use App\Models\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Collection;

class MergeGuestCartWithUserCart
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        $sessionItems = collect(session('cart.items', []));
        $dbCart = Cart::where('user_id', $user->id)->first();

        // If the user had no session cart, just load their DB cart into the session.
        if ($sessionItems->isEmpty()) {
            if ($dbCart) {
                session(['cart.items' => $dbCart->items]);
            }

            return;
        }

        // If a session cart exists, we need to merge.
        $dbItems = collect($dbCart?->items ?? []);

        // Merge session items into the database items.
        // If an item exists in both, the session (more recent) quantity wins.
        $mergedItems = $sessionItems->reduce(function (Collection $carry, array $sessionItem) {
            $existingKey = $carry->search(fn ($dbItem) => $dbItem['uniqueId'] === $sessionItem['uniqueId']);
            if ($existingKey !== false) {
                // Item exists, update quantity (or other logic)
                $carry[$existingKey]['quantity'] = $sessionItem['quantity'];
            } else {
                // New item, add it to the cart
                $carry->push($sessionItem);
            }

            return $carry;
        }, $dbItems);

        // Update the session with the fully merged cart
        session(['cart.items' => $mergedItems->values()->all()]);

        // Dispatch a job to save the final merged cart to the database.
        SyncCartToDatabaseJob::dispatch($user, $mergedItems->values()->all());
    }
}
