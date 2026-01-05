<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Models;

use App\Models\PharmacistReport;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Src\Gamification\Domain\Enums\PharmacistLevel;
use Src\Gamification\Domain\Models\GamificationLedgerEntry;
use Src\Gamification\Domain\Models\Task;
use Src\Patient\Domain\Models\Patient;
use Src\Patient\Domain\Models\PatientInteraction;
use Src\Pharmacy\Domain\Models\Community;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Questionnaire\Domain\Models\Questionnaire;
use Src\Subscription\Domain\Concerns\HasSubscription;
use Src\User\Domain\Notifications\ResetPasswordNotification;
use Src\Wallet\Domain\Concerns\HasWallet;
use Src\Wallet\Domain\Models\Wallet;

class User extends Authenticatable implements CanResetPassword, FilamentUser
{
    use CanResetPasswordTrait;

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
     * Send the password reset notification.
     *
     * This method overrides the default Laravel behavior to send our custom,
     * branded mailable.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

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
     * The Tasks assigned to this user.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
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
     * Get all of the patient interactions logged by this pharmacist for their assigned patients.
     * This is a "Has Many Through" relationship.
     *
     * We are going FROM a User TO a PatientInteraction THROUGH a Patient.
     */
    public function interactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            PatientInteraction::class, // The final model we want to get to
            Patient::class,           // The intermediate model
            'pharmacist_id',          // Foreign key on the intermediate model (patients table)
            'patient_id',             // Foreign key on the final model (patient_interactions table)
            'id',                     // Local key on the starting model (users table)
            'id'                      // Local key on the intermediate model (patients table)
        );
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

    /**
     * Get all of the weekly reports submitted by this user.
     */
    public function pharmacistReports(): HasMany
    {
        return $this->hasMany(PharmacistReport::class);
    }

    public function latestPharmacistReport(): HasOne
    {
        return $this->hasOne(PharmacistReport::class)->latestOfMany();
    }
}
