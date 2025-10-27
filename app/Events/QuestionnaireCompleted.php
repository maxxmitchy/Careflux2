<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Questionnaire\Domain\Models\QuestionnaireInvitation;

class QuestionnaireCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public QuestionnaireInvitation $invitation) {}
}
