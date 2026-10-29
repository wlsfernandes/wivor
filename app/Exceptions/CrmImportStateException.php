<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when an import action does not fit the import's current status; the message is shown to administrators. */
class CrmImportStateException extends RuntimeException
{
}
