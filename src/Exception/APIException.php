<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Exception;

use BenMorel\GLSTracker\GLSTrackerException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Thrown when receiving a well-defined error from the GLS API.
 *
 * Documented exit codes / messages:
 *
 * 0002: User account blocked
 * 0003: Missing rights (the user can access the API but doesn't have the necessary rights)
 * 0004: Input validation error
 * 0005: Missing input parameter
 * 0006: Address not supported
 * 0007: Too many search results
 * 0008: Not acceptable (the content type of the POST request is not acceptable)
 * 0009: Not authorized (invalid username or password)
 * 0010: Page not found
 * 0011: The HTTP method is not supported for this resource.
 * 9999: Unexpected error
 *
 * If the exitCode matches an exception relevant for this library, a subclass of this exception will be thrown.
 * For example, an exitCode of 0002 will throw an Exception\UserAccountBlockedException.
 *
 * If an exitCode is unknown, or is not expected to be thrown due to the way this library uses the API, a base
 * APIException is thrown, not a subclass. For example, 0010 is reported when a 404 Not Found error occurs, and
 * should never occur in the context of this library; as such, it does not need a subclass.
 */
class APIException extends GLSTrackerException
{
    private const EXCEPTION_CLASSES = [
        '0002' => APIException\UserAccountBlockedException::class,
        '0003' => APIException\MissingRightsException::class,
        '0004' => APIException\InputValidationException::class,
        '0007' => APIException\TooManySearchResultsException::class,
        '0009' => APIException\NotAuthorizedException::class
    ];

    /**
     * A 4-digit error code.
     *
     * @var string
     */
    public $exitCode;

    /**
     * A short error message.
     *
     * @var string
     */
    public $exitMessage;

    /**
     * A description of the error.
     *
     * @var string
     */
    public $description;

    /**
     * @param string               $exitCode
     * @param string               $exitMessage
     * @param string               $description
     * @param GuzzleException|null $guzzleException
     *
     * @return static
     */
    public static function create(string $exitCode, string $exitMessage, string $description, ?GuzzleException $guzzleException = null) : self
    {
        if (isset(self::EXCEPTION_CLASSES[$exitCode])) {
            $exceptionClass = self::EXCEPTION_CLASSES[$exitCode];
        } else {
            $exceptionClass = self::class;
        }

        /** @var self $exception */
        $exception = new $exceptionClass('An error occurred while querying the GLS API: ' . $description, 0, $guzzleException);

        $exception->exitCode    = $exitCode;
        $exception->exitMessage = $exitMessage;
        $exception->description = $description;

        return $exception;
    }
}
