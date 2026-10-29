<?php

namespace App\Exceptions;

use RuntimeException;

/** Raised when a campaign action is not allowed in its current state; the message is shown to administrators. */
class CrmCampaignStateException extends RuntimeException
{
}
