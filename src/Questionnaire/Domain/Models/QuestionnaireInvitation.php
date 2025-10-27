<?php

namespace Src\Questionnaire\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class QuestionnaireInvitation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * The questionnaire being sent.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * The patient who received the invitation.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The pharmacist who sent the invitation.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    /**
     * All the patient's responses for this invitation.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(PatientResponse::class);
    }
}
