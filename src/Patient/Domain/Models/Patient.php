<?php

namespace Src\Patient\Domain\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Src\Gamification\Domain\Models\Task;
use Src\Order\Domain\Models\Invoice;
use Src\Pharmacy\Domain\Models\Community;
use Src\Questionnaire\Domain\Models\QuestionnaireInvitation;
use Src\Shared\Domain\Models\User;
use Src\Wallet\Domain\Concerns\HasWallet;

class Patient extends Model
{
    use HasFactory, HasWallet;

    protected $fillable = [
        'user_id',
        'pharmacist_id',
        'family_head_patient_id',
        'community_id',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'location_area',
        'takes_regular_medications',
        'medication_list',
        'known_health_conditions',
        'last_health_check',
        'monthly_medicine_spend',
        'usual_purchase_location',
        'received_pharmacist_follow_up',
        'expectations_from_pharmacist',
        'consents_to_contact',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'takes_regular_medications' => 'boolean',
            'known_health_conditions' => 'array',
            'received_pharmacist_follow_up' => 'boolean',
            'consents_to_contact' => 'boolean',
            'expectations_from_pharmacist' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_interacted_at' => 'datetime',
        ];
    }

    /**
     * The user account associated with this patient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All questionnaire invitations sent to this patient.
     */
    public function questionnaireInvitations(): HasMany
    {
        return $this->hasMany(QuestionnaireInvitation::class);
    }

    /**
     * The pharmacist assigned to this patient.
     */
    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'subjectable');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(PatientInteraction::class)->latest();
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Static helpers for form options
    public static function getHealthConditionOptions(): array
    {
        return [
            'hypertension' => 'Hypertension (High Blood Pressure)',
            'diabetes' => 'Diabetes (High Blood Sugar)',
            'asthma' => 'Asthma',
            'arthritis' => 'Arthritis',
            'ulcer' => 'Stomach Ulcer',
            'none' => 'None of the above',
            'other' => 'Other (please specify)',
        ];
    }

    public static function getLastHealthCheckOptions(): array
    {
        return [
            '' => 'Select an option...',
            'within_last_3_months' => 'Within the last 3 months',
            '3_to_6_months_ago' => '3 to 6 months ago',
            '6_to_12_months_ago' => '6 to 12 months ago',
            'more_than_a_year_ago' => 'More than a year ago',
            'never' => 'I have never had one',
        ];
    }

    public static function getMonthlySpendOptions(): array
    {
        return [
            '' => 'Select an option...',
            '0_5k' => '₦0 - ₦5,000',
            '5k_15k' => '₦5,000 - ₦15,000',
            '15k_30k' => '₦15,000 - ₦30,000',
            '30k_plus' => 'More than ₦30,000',
        ];
    }

    /**
     * --- THIS IS THE NEW ACCESSOR ---
     * Converts the stored monthly spend string range into a representative integer in kobo.
     */
    protected function numericMonthlySpend(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => match ($attributes['monthly_medicine_spend'] ?? null) {
                '0_5k' => 250000,       // Midpoint: ₦2,500
                '5k_15k' => 1000000,     // Midpoint: ₦10,000
                '15k_30k' => 2250000,    // Midpoint: ₦22,500
                '30k_plus' => 4000000,   // An estimated average for "more than 30k"
                default => 0,
            }
        );
    }

    public static function getPurchaseLocationOptions(): array
    {
        return [
            'community_pharmacy' => 'Community pharmacy',
            'hospital_pharmacy' => 'Hospital pharmacy',
            'online_pharmacy' => 'Online pharmacy',
            'patent_medicine_store' => 'Patent medicine store',
        ];
    }

    public static function getExpectationsOptions(): array
    {
        return [
            '' => 'Select an option...',
            'medication_reminders' => 'Reminders to take my medication',
            'refill_management' => 'Help managing my refills',
            'side_effect_monitoring' => 'Someone to check for side effects',
            'cost_savings' => 'Finding ways to save money on my medicines',
            'all_of_the_above' => 'All of the above',
        ];
    }
}
