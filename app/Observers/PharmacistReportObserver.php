<?php

namespace App\Observers;

use App\Models\PharmacistReport;
use Src\Gamification\Application\Actions\AwardPointsAction;

class PharmacistReportObserver
{
    public function __construct(private AwardPointsAction $awardPointsAction) {}

    public function created(PharmacistReport $report): void
    {
        $this->awardPointsAction->execute(
            user: $report->user,
            taskKey: 'PHARMACIST_WEEKLY_REPORT',
            subjectable: $report
        );
    }
}
