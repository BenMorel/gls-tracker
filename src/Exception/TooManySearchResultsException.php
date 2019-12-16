<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerAPIException;

/**
 * Thrown when too many search results would be returned.
 * Request less trackIDs when this occurs.
 */
class TooManySearchResultsException extends GLSTrackerAPIException
{
}
