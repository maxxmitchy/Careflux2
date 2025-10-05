<?php

namespace App\Jobs;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Shared\Domain\Models\User;

class SyncCartToDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $cartItems
    ) {}

    public function handle(): void
    {
        // Use updateOrCreate to handle both new and existing carts seamlessly.
        Cart::updateOrCreate(
            ['user_id' => $this->user->id],
            ['items' => $this->cartItems]
        );
    }
}
