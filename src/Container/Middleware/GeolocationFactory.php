<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container\Middleware;

use NetglueGeoIP\Middleware\Geolocation;
use NetglueGeoIP\Service\GeoIPService;
use Psr\Container\ContainerInterface;

class GeolocationFactory
{
    public function __invoke(ContainerInterface $container) : Geolocation
    {
        return new Geolocation($container->get(GeoIPService::class));
    }
}
