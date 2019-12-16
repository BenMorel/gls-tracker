<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerException;

/**
 * Thrown when an invalid response has been received from the API, and this library cannot decode it.
 */
class InvalidResponseException extends GLSTrackerException
{
}
