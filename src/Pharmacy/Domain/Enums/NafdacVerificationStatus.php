<?php

namespace Src\Pharmacy\Domain\Enums;

enum NafdacVerificationStatus: string
{
    case UNVERIFIED = 'unverified';
    case VERIFIED = 'verified';
    case MISMATCHED = 'mismatched';
}
