<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Gamification\Domain\Enums\PharmacistLevel;
use Src\Shared\Domain\Models\User;

class UserLeveledUp
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user, public PharmacistLevel $newLevel) {}
}
