<?php

namespace Src\Questionnaire\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Shared\Domain\Models\User;

class Questionnaire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The user (Admin or Pharmacist) who created this questionnaire.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * The top-level questions in this questionnaire (questions without a parent answer).
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->whereNull('parent_answer_option_id')->orderBy('order');
    }

    /**
     * All questions, including nested ones.
     */
    public function allQuestions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * The invitations sent out for this questionnaire.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(QuestionnaireInvitation::class);
    }
}
