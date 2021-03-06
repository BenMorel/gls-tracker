<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker;

use BenMorel\GLSTracker\Exception\APIException;
use BenMorel\GLSTracker\Exception\InvalidResponseException;
use BenMorel\GLSTracker\Exception\NetworkException;
use BenMorel\GLSTracker\Model\Event;
use BenMorel\GLSTracker\Model\Parcel;
use BenMorel\GLSTracker\Model\Reference;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

class GLSTracker
{
    private const BASE_URL = 'https://api.gls-group.eu/public/v1/tracking/references/';

    /**
     * The Guzzle HTTP client.
     *
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $language;

    /**
     * GLSTrackingClient constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $language
     */
    public function __construct(string $username, string $password, string $language = 'en')
    {
        $this->httpClient = new Client();

        $this->username = $username;
        $this->password = $password;
        $this->language = $language;
    }

    /**
     * Tracks a single parcel by TrackID.
     *
     * @param string $trackID
     *
     * @return Parcel|null The parcel, or null if not found.
     *
     * @throws GLSTrackerException If an error occurs.
     */
    public function trackOne(string $trackID) : ?Parcel
    {
        $parcels = $this->trackMany($trackID);

        return $parcels[0] ?? null;
    }

    /**
     * Tracks several parcels by TrackID.
     *
     * The resulting array may contain less parcels than requested, if some parcels are not found.
     *
     * @param string ...$trackIDs Zero or more TrackIDs.
     *
     * @return Parcel[] The Parcel models.
     *
     * @throws GLSTrackerException If an error occurs.
     */
    public function trackMany(string ...$trackIDs) : array
    {
        if (! $trackIDs) {
            return [];
        }

        $trackIDs = implode(',', $trackIDs);

        /** @var GuzzleException|null $guzzleException */
        $guzzleException = null;

        /** @var ResponseInterface|null $response */
        $response = null;

        try {
            $response = $this->httpClient->request('GET', self::BASE_URL . $trackIDs, [
                RequestOptions::AUTH => [
                    $this->username,
                    $this->password
                ],
                RequestOptions::HEADERS => [
                    'Accept-Language' => $this->language
                ]
            ]);
        } catch (GuzzleException $e) {
            $guzzleException = $e;

            if ($e instanceof RequestException) {
                $response = $e->getResponse();
            }
        }

        /** @var array|null $jsonResponse */
        $jsonResponse = null;

        if ($response !== null) {
            $jsonResponse = $this->getJsonResponse($response, $guzzleException);
        }

        if ($guzzleException === null) {
            return $this->jsonResponseToParcels($jsonResponse);
        }

        if ($response === null) {
            throw new NetworkException('A network error occurred while querying the GLS API.', 0, $guzzleException);
        }

        $error = $jsonResponse['errors'][0];

        throw APIException::create(
            $error['exitCode'],
            $error['exitMessage'],
            $error['description'],
            $guzzleException
        );
    }

    /**
     * @param ResponseInterface    $response
     * @param GuzzleException|null $guzzleException
     *
     * @return array The decoded JSON response data.
     *
     * @throws GLSTrackerException
     */
    private function getJsonResponse(ResponseInterface $response, ?GuzzleException $guzzleException) : array
    {
        $contentType = $response->getHeaderLine('Content-Type');

        $pos = strpos($contentType, ';');

        if ($pos !== false) {
            $contentType = substr($contentType, 0, $pos);
        }

        if ($contentType !== 'application/json') {
            throw new InvalidResponseException('The HTTP API response is not a JSON document.', 0, $guzzleException);
        }

        $json = (string) $response->getBody();

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new InvalidResponseException('The HTTP API response contains a malformed JSON document.', 0, $jsonException);
        }
    }

    /**
     * @param array $jsonResponse The decoded JSON response data.
     *
     * @return Parcel[] The Parcel models.
     */
    private function jsonResponseToParcels(array $jsonResponse) : array
    {
        $parcelModels = [];

        foreach ($jsonResponse['parcels'] as $parcel) {
            $parcelModel = new Parcel();

            $parcelModel->timestamp = $parcel['timestamp'];
            $parcelModel->status    = $parcel['status'];
            $parcelModel->trackid   = $parcel['trackid'];

            foreach ($parcel['references'] as $reference) {
                $referenceModel = new Reference();

                $referenceModel->type  = $reference['type'];
                $referenceModel->name  = $reference['name'];
                $referenceModel->value = $reference['value'];

                $parcelModel->references[] = $referenceModel;
            }

            foreach ($parcel['events'] as $event) {
                $eventModel = new Event();

                $eventModel->timestamp   = $event['timestamp'];
                $eventModel->description = $event['description'];
                $eventModel->location    = $event['location'];
                $eventModel->country     = $event['country'];
                $eventModel->code        = $event['code'];

                $parcelModel->events[] = $eventModel;
            }

            $parcelModels[] = $parcelModel;
        }

        return $parcelModels;
    }
}
