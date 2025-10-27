<?php

namespace App\Observers;

use Src\Medication\Domain\Models\Medication;

class MedicationObserver
{
    public function saving(Medication $medication): void
    {
        if ($medication->isDirty('name')) {
            $medication->soundex_name = soundex($medication->name);
        }
    }
}
