<?php

namespace Src\Questionnaire\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientResponse extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The invitation this response is for.
     */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireInvitation::class, 'questionnaire_invitation_id');
    }

    /**
     * The question being answered.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The chosen answer option (for radio/checkbox).
     */
    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class);
    }
}
