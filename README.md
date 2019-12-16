# GLS Tracker

A PHP library to track GLS parcels using the GLS Track & Trace Web API.

[![Latest Stable Version](https://poser.pugx.org/benmorel/gls-tracker/v/stable)](https://packagist.org/packages/benmorel/gls-tracker)

## Installation

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require benmorel/gls-tracker
```

## Requirements

This library requires PHP 7.1 or later.

## Project status & release process

This library is under development.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing
existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.1.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/BenMorel/gls-tracker/releases)
for a list of changes introduced by each further `0.x.0` version.

## Quickstart

Instantiate the tracker with your GLS API username & password, and start querying:

```php
use BenMorel\GLSTracker\GLSTracker;

// Instantiate the tracker
$tracker = new GLSTracker('username', 'password');

// Track one or more parcels
$parcels = $tracker->track('00AB1234', '00XY6789');

// Loop through the parcels

foreach ($parcels as $parcel) {
    echo $parcel->trackid, ' ', $parcel->status, PHP_EOL;

    foreach ($parcel->events as $event) {
        echo $event->timestamp, ' ', $event->description, PHP_EOL;
    }
}
```

Note that `track()` returns the `Parcel` objects indexed by TrackID, so you can alternatively look them up directly:

```
$parcels = $tracker->track('00AB1234');

if (isset($parcels['00AB1234'])) {
    $parcel = $parcels['00AB1234'];
    echo $parcel->trackid, ' ', $parcel->status, PHP_EOL;
} else {
    // parcel not found
}
```

Note that there is a limit on the number of the number of search results that may be returned by the API in a single call.
If you request too many trackIDs at a time, you'll get a `TooManySearchResultsException`.
