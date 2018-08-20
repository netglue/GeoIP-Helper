<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container\Service;

use MaxMind\Db\Reader;
use NetglueGeoIP\Service\GeoIPService;
use Psr\Container\ContainerInterface;

class GeoIPServiceFactory
{
    public function __invoke(ContainerInterface $container) : GeoIPService
    {
        $config = $container->get('config');
        $locales = null;
        if (isset($config['geoip']['locales'])) {
            $locales = $config['geoip']['locales'];
        }
        return new GeoIPService(
            $container->get(Reader::class),
            $locales
        );
    }
}
