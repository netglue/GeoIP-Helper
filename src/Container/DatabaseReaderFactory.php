<?php
declare(strict_types=1);

namespace NetglueGeoIP\Container;

use MaxMind\Db\Reader;
use NetglueGeoIP\Exception;
use Psr\Container\ContainerInterface;
use Throwable;

class DatabaseReaderFactory
{
    public function __invoke(ContainerInterface $container) : Reader
    {
        $config = $container->get('config');
        $file = isset($config['geoip']['databaseFile'])
            ? $config['geoip']['databaseFile']
            : null;
        if (! $file) {
            throw new Exception\ConfigException('The GeoIP Database location has not been configured');
        }
        try {
            return new Reader($file);
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Failed to create a GeoIP database reader instance', 500, $e);
        }
    }
}
