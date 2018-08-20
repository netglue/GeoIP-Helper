<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Helper\ClientIPFromSuperGlobals;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIP\ZendMvc\Controller\Plugin\GeoIP;
use Psr\Container\ContainerInterface;

class GeoIPFactory
{
    public function __invoke(ContainerInterface $container) : GeoIP
    {
        return new GeoIP(
            $container->get(GeoIPService::class),
            $container->get(ClientIPFromSuperGlobals::class)
        );
    }
}
