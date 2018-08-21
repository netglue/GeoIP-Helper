<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Container\ZendMvc\Controller\Plugin\GeoIPFactory;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIP\ZendMvc\Controller\Plugin\GeoIP;
use NetglueGeoIPTest\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class GeoIPTest extends TestCase
{
    /** @var GeoIPService|ObjectProphecy */
    private $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->prophesize(GeoIPService::class);
    }

    public function getPlugin() : GeoIP
    {
        return new GeoIP($this->service->reveal());
    }

    public function testInvokeReturnsSelf()
    {
        $plugin = $this->getPlugin();
        $this->assertSame($plugin, $plugin());
    }

    public function testGetServiceReturnsService()
    {
        $plugin = $this->getPlugin();
        $this->assertInstanceOf(GeoIPService::class, $plugin->getService());
    }

    public function testGet()
    {
        $this->service->get('1.1.1.1')->willReturn(['foo' => 'foo']);
        $plugin = $this->getPlugin();
        $result = $plugin->get('1.1.1.1');
        $this->assertSame('foo', $result['foo']);
    }

    public function testCountryCode()
    {
        $this->service->countryCode('1.1.1.1')->willReturn('GB');
        $plugin = $this->getPlugin();
        $this->assertSame('GB', $plugin->countryCode('1.1.1.1'));
    }

    public function testCountryName()
    {
        $this->service->countryName('1.1.1.1')->willReturn('GB');
        $plugin = $this->getPlugin();
        $this->assertSame('GB', $plugin->countryName('1.1.1.1'));
    }

    public function testTimezone()
    {
        $this->service->timezone('1.1.1.1')->willReturn('whatever');
        $plugin = $this->getPlugin();
        $this->assertSame('whatever', $plugin->timezone('1.1.1.1'));
    }

    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(GeoIPService::class)->willReturn($this->service->reveal());

        $factory = new GeoIPFactory();
        $plugin = $factory($container->reveal());
        $this->assertInstanceOf(GeoIP::class, $plugin);
    }
}
