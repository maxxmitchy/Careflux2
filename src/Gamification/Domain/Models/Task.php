<?php

namespace Src\Gamification\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Shared\Domain\Models\User;

class Task extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships: taskDefinition(), assignedTo(), createdBy(), subjectable()

    public function taskDefinition()
    {
        return $this->belongsTo(TaskDefinition::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function subjectable()
    {
        return $this->morphTo();
    }
}
