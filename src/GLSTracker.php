<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker;

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
     * GLSTrackingClient constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->httpClient = new Client();

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Tracks one or more parcels by TrackID.
     *
     * @param string ...$trackIDs One or more TrackIDs.
     *
     * @return Parcel[] The Parcel models, indexed by trackID.
     *
     * @throws InvalidArgumentException If no trackIDs are given.
     * @throws GLSTrackerException      If an error occurs while communicating with the API.
     */
    public function track(string ...$trackIDs) : array
    {
        if (! $trackIDs) {
            throw new InvalidArgumentException('Missing track IDs.');
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
            throw new GLSTrackerException('A network error occurred while querying the GLS API.', 0, $guzzleException);
        }

        $error = $jsonResponse['errors'][0];

        throw GLSTrackerAPIException::create(
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
            throw new GLSTrackerException('The HTTP API response is not a JSON document.', 0, $guzzleException);
        }

        $json = (string) $response->getBody();

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new GLSTrackerException('The HTTP API response contains a malformed JSON document.', 0, $jsonException);
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

            $parcelModels[$parcelModel->trackid] = $parcelModel;
        }

        return $parcelModels;
    }
}
