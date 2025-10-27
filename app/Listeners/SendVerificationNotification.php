<?php

namespace App\Listeners;

use App\Events\VerificationInitiated;
use App\Jobs\NotifyPharmacistOfVerificationRequest; // We will create this job

class SendVerificationNotification
{
    public function handle(VerificationInitiated $event): void
    {
        // Dispatch the job to the queue for asynchronous processing
        NotifyPharmacistOfVerificationRequest::dispatch($event->verification);
    }
}
