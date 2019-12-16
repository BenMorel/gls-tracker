<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception\APIException;

use BenMorel\GLSTracker\Exception\APIException;

/**
 * Thrown when too many search results would be returned.
 * Request less trackIDs when this occurs.
 */
class TooManySearchResultsException extends APIException
{
}
