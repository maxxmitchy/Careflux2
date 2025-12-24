<?php

namespace Src\User\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Shared\Domain\Models\User; // Use our primary User model

class UserSignedUp
{
    use Dispatchable, SerializesModels;

    /**
     * The newly created user instance.
     */
    public User $user;

    /**
     * The role the user signed up as.
     */
    public string $role;

    /**
     * Create a new event instance.
     *
     * @param  string  $role  ('patient', 'technician', 'pharmacist')
     * @return void
     */
    public function __construct(User $user, string $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}
