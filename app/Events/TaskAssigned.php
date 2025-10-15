<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Gamification\Domain\Models\Task;

class TaskAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Task $task) {}
}
