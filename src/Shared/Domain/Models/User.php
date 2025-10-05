<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Src\Gamification\Domain\Enums\PharmacistLevel;
use Src\Gamification\Domain\Models\GamificationLedgerEntry;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\Community;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Questionnaire\Domain\Models\Questionnaire;
use Src\Subscription\Domain\Concerns\HasSubscription;
use Src\Wallet\Domain\Concerns\HasWallet;
use Src\Wallet\Domain\Models\Wallet;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasSubscription, HasWallet, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'is_pharmacist',
        'is_technician',
        'is_patient',
        'is_technician',
        'is_manager',
        'is_pharmacy_technician',
        'pharmacy_id',
        'public_profile_id',
        'email_verified_at',
        'verified_at',
        'activated_at',
        'avatar_url',
        'points_balance',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activated_at' => 'datetime',
            'level' => PharmacistLevel::class,
            'is_admin' => 'boolean',
            'is_pharmacist' => 'boolean',
            'is_technician' => 'boolean',
            'is_patient' => 'boolean',
            'verified_at' => 'datetime',
            'points_balance' => 'integer',
            'is_manager' => 'boolean',
        ];
    }

    // ======================================================================
    // Panel Access Control
    // ======================================================================

    /**
     * Determines which Filament panels a user can access.
     * This is the master gatekeeper for our entire application.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->is_admin,
            'pharmacy' => $this->is_pharmacist,
            'technician' => $this->is_technician,
            'patient' => $this->is_patient,
            default => false,
        };
    }

    // ======================================================================
    // Relationships
    // ======================================================================

    /**
     * The Pharmacy this user works for (if they are a Pharmacist or Tech).
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The Patient profile associated with this user account.
     */
    public function patientProfile(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function assignedPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'pharmacist_id');
    }

    /**
     * The user's wallet for holding credits and rewards.
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    /**
     * The gamification points history for this user.
     */
    public function gamificationLedgerEntries(): HasMany
    {
        return $this->hasMany(GamificationLedgerEntry::class);
    }

    public function communities(): MorphMany
    {
        return $this->morphMany(Community::class, 'owner');
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class, 'created_by_user_id');
    }
}
