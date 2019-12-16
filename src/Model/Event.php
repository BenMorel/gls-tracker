<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Model;

class Event
{
    /**
     * The timestamp of the event.
     *
     * Example: 2019-12-12T12:29:41
     *
     * @var string
     */
    public $timestamp;

    /**
     * The description of the event.
     *
     * Example: The parcel has been delivered.
     *
     * @var string
     */
    public $description;

    /**
     * The location of the event.
     *
     * Example: Vitry sur Seine
     *
     * @var string
     */
    public $location;

    /**
     * The country code.
     *
     * Example: FR
     *
     * @var string
     */
    public $country;

    /**
     * The event code.
     *
     * Example: 3.0
     *
     * @var string
     */
    public $code;
}
