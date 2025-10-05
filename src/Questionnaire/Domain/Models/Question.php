<?php

namespace Src\Questionnaire\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The questionnaire this question belongs to.
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * The answer option that triggers this question to appear (for nested logic).
     */
    public function parentAnswerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class, 'parent_answer_option_id');
    }

    /**
     * The predefined answer options for this question (if it's a radio or checkbox type).
     */
    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class)->orderBy('order');
    }

    public function childQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'parent_answer_option_id', 'id');
    }
}
