<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerAPIException;

/**
 * Thrown when the user can access the API but doesn't have the necessary rights.
 */
class MissingRightsException extends GLSTrackerAPIException
{
}
