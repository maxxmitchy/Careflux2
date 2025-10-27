<?php

namespace Src\Order\Domain\Exceptions;

use Exception;

class InvalidCartQuantityException extends Exception
{
    // This custom exception allows us to specifically catch this error type.
}
