<?php

declare(strict_types=1);

namespace Src\Gamification\Domain\Enums;

enum PharmacistLevel: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
}
