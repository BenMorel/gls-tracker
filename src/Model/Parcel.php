<?php

declare(strict_types=1);

namespace BenMorel\GLSTracker\Model;

class Parcel
{
    /**
     * The timestamp of the last event.
     *
     * Example: 2019-12-12T12:29:41
     *
     * @var string
     */
    public $timestamp;

    /**
     * The status of the parcel.
     *
     * Example: DELIVERED
     *
     * @var string
     */
    public $status;

    /**
     * The track ID.
     *
     * Example: 00AB1234
     *
     * @var string
     */
    public $trackid;

    /**
     * The parcel references.
     *
     * @var Reference[]
     */
    public $references = [];

    /**
     * The parcel events.
     *
     * @var Event[]
     */
    public $events = [];
}
