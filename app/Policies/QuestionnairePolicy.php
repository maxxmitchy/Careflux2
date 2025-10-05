<?php

namespace App\Policies;

use Src\Questionnaire\Domain\Models\Questionnaire;
use Src\Shared\Domain\Models\User;

class QuestionnairePolicy
{
    /**
     * Admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    /**
     * A user can view a questionnaire if it's a template OR they created it.
     */
    public function view(User $user, Questionnaire $questionnaire): bool
    {
        return $questionnaire->is_template || $questionnaire->created_by_user_id === $user->id;
    }

    /**
     * A user can only update or delete a questionnaire if they created it.
     * Admins are handled by the before() method.
     */
    public function update(User $user, Questionnaire $questionnaire): bool
    {
        return $questionnaire->created_by_user_id === $user->id;
    }

    public function delete(User $user, Questionnaire $questionnaire): bool
    {
        return $questionnaire->created_by_user_id === $user->id;
    }
}
