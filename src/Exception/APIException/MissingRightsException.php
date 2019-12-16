<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception\APIException;

use BenMorel\GLSTracker\Exception\APIException;

/**
 * Thrown when the user can access the API but doesn't have the necessary rights.
 */
class MissingRightsException extends APIException
{
}
