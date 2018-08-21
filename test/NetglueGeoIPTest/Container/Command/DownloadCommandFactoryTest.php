<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Container\Command;

use NetglueGeoIP\Command\DownloadCommand;
use NetglueGeoIP\Container\Command\DownloadCommandFactory;
use NetglueGeoIPTest\TestCase;
use Psr\Container\ContainerInterface;

class DownloadCommandFactoryTest extends TestCase
{
    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'geoip' => []
        ]);
        $factory = new DownloadCommandFactory;
        $command = $factory($container->reveal());
        $this->assertInstanceOf(DownloadCommand::class, $command);
    }
}
