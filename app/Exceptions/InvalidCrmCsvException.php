<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when an uploaded participant CSV cannot be read safely; the message is shown to administrators. */
class InvalidCrmCsvException extends RuntimeException
{
}
