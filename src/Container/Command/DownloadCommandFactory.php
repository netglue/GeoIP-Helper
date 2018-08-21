<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container\Command;

use NetglueGeoIP\Command\DownloadCommand;
use Psr\Container\ContainerInterface;

class DownloadCommandFactory
{
    public function __invoke(ContainerInterface $container) : DownloadCommand
    {
        $config = $container->get('config');
        $config = isset($config['geoip']) ? $config['geoip'] : null;
        return new DownloadCommand($config);
    }
}
