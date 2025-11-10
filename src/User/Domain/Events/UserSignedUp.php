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
     * @var \Src\Shared\Domain\Models\User
     */
    public User $user;

    /**
     * The role the user signed up as.
     * @var string
     */
    public string $role;

    /**
     * Create a new event instance.
     *
     * @param  \Src\Shared\Domain\Models\User  $user
     * @param  string  $role ('patient', 'technician', 'pharmacist')
     * @return void
     */
    public function __construct(User $user, string $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}