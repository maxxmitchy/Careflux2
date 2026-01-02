<?php

namespace Src\Gamification\Domain\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
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

    // alias for readability / compatibility with Filament columns using "assignee"
    public function assignee()
    {
        // either delegate to the existing relation:
        return $this->assignedTo();
        // or explicitly:
        // return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * The primary subject of the task (for single-subject tasks).
     */
    public function subjectable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- THIS IS THE DEFINITIVE FIX ---
    /**
     * The Pharmacy Products associated with this task (for batch tasks).
     * We explicitly define the relationship name, pivot table, foreign key, and related key.
     */
    public function pharmacyProducts(): MorphToMany
    {
        return $this->morphedByMany(
            PharmacyProduct::class, // The related model
            'subjectable',          // The name of the relationship
            'task_subjectables',    // The pivot table name
            'task_id',              // The foreign key on the pivot table for THIS model
            'subjectable_id'       // The foreign key on the pivot table for the RELATED model
        );
    }

    /**
     * Provide subjectable_text used by Filament column.
     */
    public function getSubjectableTextAttribute(): string
    {
        if (! $this->subjectable) {
            return 'N/A';
        }

        // common name attributes
        return $this->subjectable->name
            ?? $this->subjectable->full_name
            ?? (method_exists($this->subjectable, 'getKey') ? (string) $this->subjectable->getKey() : 'N/A');
    }

    /**
     * An accessor to generate a human-readable description of all subjects
     * associated with this task, ensuring high performance by eager-loading.
     */
    protected function subjectsDescription(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Case 1: The task has multiple product subjects
                // --- THIS IS THE DEFINITIVE, N+1 SAFE FIX ---
                if ($this->relationLoaded('pharmacyProducts')) {
                    // If the relation is already loaded, we can work with it directly.
                    $products = $this->pharmacyProducts;
                } else {
                    // If not loaded, we must eager-load the entire chain.
                    $products = $this->pharmacyProducts()->with([
                        'medicationVariant.medication',
                    ])->get();
                }

                if ($products->isNotEmpty()) {
                    $count = $products->count(); // Use count on the loaded collection
                    $names = $products->take(3)->pluck('name')->implode(', ');

                    return "{$count} Product(s): {$names}".($count > 3 ? '...' : '');
                }
                // --- END OF FIX ---

                // Case 2: The task has a single polymorphic subject
                if ($this->subjectable) {
                    if ($this->subjectable instanceof \Src\Patient\Domain\Models\Patient) {
                        return 'Patient: '.$this->subjectable->full_name;
                    }
                    if ($this->subjectable instanceof \App\Models\PharmacistReport) {
                        return 'Report from '.$this->subjectable->user->name;
                    }
                }

                // Default fallback
                return 'N/A';
            }
        );
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_at < now();
    }
}
