# GLS Tracker

A PHP library to track GLS parcels using the GLS Track & Trace Web API.

[![Latest Stable Version](https://poser.pugx.org/benmorel/gls-tracker/v/stable)](https://packagist.org/packages/benmorel/gls-tracker)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

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

First, instantiate the tracker with your GLS API username & password:

```php
use BenMorel\GLSTracker\GLSTracker;
use BenMorel\GLSTracker\GLSTrackerException;

// Instantiate the tracker
$tracker = new GLSTracker('username', 'password');
```

You can optionally pass a language code as third parameter, to get results returned in another language (defaults to `en`):

```php
$tracker = new GLSTracker('username', 'password', 'fr');
```

You can now track a single parcel:

```php
try {
    $parcel = $tracker->trackOne('00AB1234');

    if ($parcel !== null) {
        echo $parcel->trackid, ' ', $parcel->status, PHP_EOL;
    
        foreach ($parcel->events as $event) {
            echo "\t", $event->timestamp, ' ', $event->description, PHP_EOL;
        }
    } else {
        echo 'Parcel not found!', PHP_EOL;
    }
} catch (GLSTrackerException $e) {
    // an error occurred
    echo $e;
}
```

Or track several parcels at once:

```php
try {
    $parcels = $tracker->trackMany('00AB1234', '00XY6789');

    foreach ($parcels as $parcel) {
        echo $parcel->trackid, ' ', $parcel->status, PHP_EOL;

        foreach ($parcel->events as $event) {
            echo "\t", $event->timestamp, ' ', $event->description, PHP_EOL;
        }
    }
} catch (GLSTrackerException $e) {
    // an error occurred
    echo $e;
}
```

Note that there is a limit on the number of search results that may be returned by the API in a single call.
If you request too many TrackIDs at a time, you may get a `TooManySearchResultsException`.

## Error handling

If an error occurs while querying the API, a `GLSTrackerException` is always thrown.

Exception hierarchy:

- `GLSTrackerException`  
Base class for all exceptions.
    - `Exception\APIException`  
    A well-known error response has been received from the API.
        - `Exception\APIException\InputValidationException`  
        An invalid TrackID has been given.
        - `Exception\APIException\MissingRightsException`  
        The user can access the API but doesn't have the necessary rights.
        - `Exception\APIException\NotAuthorizedException`  
        The username or password is incorrect.
        - `Exception\APIException\TooManySearchResultsException`  
        Too many search results would be returned; you should request less TrackIDs.
        - `Exception\APIException\UserAccountBlockedException`  
        The user account is blocked.
    - `Exception\InvalidResponseException`  
    An invalid response has been received from the API, and this library cannot decode it.
    - `Exception\NetworkException`  
    A network error occurred and no response has been received from the API.

These fine-grained exceptions allow you to act automatically upon each type of failure.
For example, when receiving a `NetworkException`, you may schedule a retry a few moments later.
