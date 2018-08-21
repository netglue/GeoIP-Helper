<?php
declare(strict_types=1);

namespace NetglueGeoIP\Middleware;

use NetglueGeoIP\Service\GeoIPService;
use NetglueRealIP\Middleware\IpAddress;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Geolocation implements MiddlewareInterface
{

    public const COUNTRY_CODE = 'geo_country_code';

    public const COUNTRY_NAME = 'geo_country_name';

    public const TIMEZONE = 'geo_timezone';

    public const DATA = 'geo_data';

    /** @var GeoIPService */
    private $service;

    public function __construct(GeoIPService $geoIPService)
    {
        $this->service = $geoIPService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $ip = $request->getAttribute(IpAddress::ATTRIBUTE);
        if ($ip) {
            $request = $this->injectGeolocationData($request, $ip);
        }
        return $handler->handle($request);
    }

    private function injectGeolocationData(ServerRequestInterface $request, string $ip) : ServerRequestInterface
    {
        $data = $this->service->get($ip);
        if (empty($data)) {
            return $request;
        }
        $request = $request->withAttribute(self::DATA, $data);
        $code = $this->service->countryCode($ip);
        if ($code) {
            $request = $request->withAttribute(self::COUNTRY_CODE, $code);
        }
        $name = $this->service->countryName($ip);
        if ($name) {
            $request = $request->withAttribute(self::COUNTRY_NAME, $name);
        }
        $timezone = $this->service->timezone($ip);
        if ($timezone) {
            $request = $request->withAttribute(self::TIMEZONE, $timezone);
        }
        return $request;
    }
}
