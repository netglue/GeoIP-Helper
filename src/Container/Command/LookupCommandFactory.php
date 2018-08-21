<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container\Command;

use NetglueGeoIP\Command\LookupCommand;
use NetglueGeoIP\Service\GeoIPService;
use Psr\Container\ContainerInterface;

class LookupCommandFactory
{
    public function __invoke(ContainerInterface $container) : LookupCommand
    {
        return new LookupCommand($container->get(GeoIPService::class));
    }
}
