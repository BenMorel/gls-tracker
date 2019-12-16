<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerAPIException;

/**
 * Thrown when the username or password is incorrect.
 */
class NotAuthorizedException extends GLSTrackerAPIException
{
}
