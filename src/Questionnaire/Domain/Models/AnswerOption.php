<?php

namespace Src\Questionnaire\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnswerOption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The question these options belong to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * The sub-questions that are triggered when this option is selected.
     */
    public function childQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'parent_answer_option_id')->orderBy('order');
    }
}
