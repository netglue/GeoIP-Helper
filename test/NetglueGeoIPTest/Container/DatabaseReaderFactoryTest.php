<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Container;

use MaxMind\Db\Reader;
use NetglueGeoIP\Container\DatabaseReaderFactory;
use NetglueGeoIPTest\TestCase;
use Psr\Container\ContainerInterface;

class DatabaseReaderFactoryTest extends TestCase
{
    /**
     * @expectedException \NetglueGeoIP\Exception\ConfigException
     * @expectedExceptionMessage The GeoIP Database location has not been configured
     */
    public function testMissingDbPathThrowsException()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([]);

        $factory = new DatabaseReaderFactory();
        $factory($container->reveal());
    }

    /**
     * @expectedException \NetglueGeoIP\Exception\RuntimeException
     * @expectedExceptionMessage Failed to create a GeoIP database reader instance
     */
    public function testNonDBFileCausesException()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'geoip' => [
                'databaseFile' => __FILE__,
            ],
        ]);

        $factory = new DatabaseReaderFactory();
        $factory($container->reveal());
    }

    public function testReaderCanBeCreated()
    {
        $file = 'vendor/maxmind/geoip-test-data/test-data/GeoIP2-City-Test.mmdb';
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'geoip' => [
                'databaseFile' => $file,
            ],
        ]);
        $factory = new DatabaseReaderFactory();
        $reader = $factory($container->reveal());
        $this->assertInstanceOf(Reader::class, $reader);
    }
}
