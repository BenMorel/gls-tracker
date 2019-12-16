<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerException;

/**
 * Thrown when a network error occurs and no response has been received from the API.
 */
class NetworkException extends GLSTrackerException
{
}
