# Geo Location Helper/Middleware for Zend Framework/Expressive Projects

[![Latest Stable Version](https://poser.pugx.org/netglue/geoip2-helpers/version)](https://packagist.org/packages/netglue/geoip2-helpers)
[![Coverage Status](https://coveralls.io/repos/github/netglue/GeoIP-Helper/badge.svg)](https://coveralls.io/github/netglue/GeoIP-Helper)
[![Build Status](https://travis-ci.org/netglue/GeoIP-Helper.svg?branch=master)](https://travis-ci.org/netglue/GeoIP-Helper)
[![Maintainability](https://api.codeclimate.com/v1/badges/51974dc4cfd63d62f5f3/maintainability)](https://codeclimate.com/github/netglue/GeoIP-Helper/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/51974dc4cfd63d62f5f3/test_coverage)](https://codeclimate.com/github/netglue/GeoIP-Helper/test_coverage)

## About

This lib is useful if you are only interested in using MaxMind’s GeoLite2 databases to figure out the country of origin
and maybe a bit more detail such as city (slower) based on the IP address of the visitor inside a Zend Expressive or PSR-15 app,
or a Zend Framework 3 based app. Factories and default configuration are setup out of the box for both Expressive and ZF3.

Retrieving location data for an IP address relies on the ability to figure out the remote address of the client, so this
lib has a dependency on [netglue/realip-helpers](https://github.com/netglue/RealIP-Helpers). This is only used by the middleware.

## Install

Install with composer using `"netglue/geoip2-helpers"`

Zend’s component installer

## Get a copy of the database

This package comes with a utility for downloading the free, GeoLite2 databases. If you are using a paid version, you’ll
probably be downloading the databases into a central location, so MaxMind’s `geoipupdate` will likely be a better tool
for you: [https://github.com/maxmind/geoipupdate](https://github.com/maxmind/geoipupdate).

### Using the shipped downloader tool

After installing with composer, the command will be made available in `vendor/bin/geoip`. You can get help for the 
command with `geoip help download`.

With no arguments, the command will download both the city and country databases into the `./data` directory of this lib.
To download to a different directory, you should provide a directory argument, i.e `geoip download ~/geoip-data/here`.

Files will be named whatever they are on the remote server, something like `GeoLite2-City.mmdb`.

If you are only interested in Country level information, you can turn off downloading the city database with the `--country`
switch and vice-versa with `--city`. By default, both databases will be retrieved.

The `--no-clobber` switch will only perform a download if the files do not already exist so it can be used during deployment
for example to avoid repeatedly downloading stuff you don't need.

## ZF3 Controller Plugin

In a ZF3 app, the default config exposes a plugin with the name `geoIp`. Using this plugin is pretty straight forward:

```php
$ip = $this->clientIp(); // If you are using the netglue/realip-helpers dependency
if ($ip) {
    $countryCode = $this->geoIp()->countryCode($ip);
    $countryName = $this->geoIp()->countryName($ip);
    $timezone    = $this->geoIp()->timezone($ip); // If you are using the City Database
    $dataArray   = $this->geoIp()->get($ip);
}
```

## PSR-15 Middleware

Add `\NetglueGeoIP\Middleware\Geolocation::class` to your pipeline, after a middleware that will inject a request attribute
of `ip_address` _(By default, can be configured in construct)_ containing the client IP.

Subsequent middleware will have access to the attributes:

* `Geolocation::DATA` - An array containing everything that was found in the database (If anything)
* `Geolocation::COUNTRY_CODE` - 2 letter ISO country code
* `Geolocation::COUNTRY_NAME` - Country name according to configured locales
* `Geolocation::TIMEZONE` - Timezone, if available - depends on using the city based database.

## Alternative stuff to look at

This lib might be too narrow or specific so you might find that [Geocoder PHP](http://geocoder-php.org) is a more mature,
better fit for your needs. There's PSR-15 middleware out there that consumes this lib:
[middlewares/geolocation](https://github.com/middlewares/geolocation)

## Test

`cd` to wherever the module is installed, issue a `composer install` followed by a `composer test`.

## Contributions

PR's are welcomed. Please write tests for new features.

## Support

You're welcome to file issues, but please understand that finding the time to answer support requests is very limited
so there might be a long wait for an answer.


## About

[Netglue makes websites and apps in Devon, England](https://netglue.uk).
We hope this is useful to you and we’d appreciate feedback either way :)

