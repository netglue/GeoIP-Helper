<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Service;

use MaxMind\Db\Reader;
use NetglueGeoIP\Container\Service\GeoIPServiceFactory;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIPTest\TestCase;
use Psr\Container\ContainerInterface;

class GeoIPServiceTest extends TestCase
{
    /**
     * @expectedException \NetglueGeoIP\Exception\InvalidIpAddressException
     */
    public function testGetThrowsExceptionForInvalidIpAddress()
    {
        $reader = $this->prophesize(Reader::class);
        $service = new GeoIPService($reader->reveal());
        $service->get('Foo');
    }

    public function testRecordRetrieval()
    {
        $reader = new Reader('vendor/maxmind/geoip-test-data/test-data/GeoIP2-City-Test.mmdb');
        $ip = '81.2.69.160';
        $service = new GeoIPService($reader);
        $data = $service->get($ip);
        $this->assertInternalType('array', $data);
        $this->assertSame('London', $data['city']['names']['en']);

        // Continent
        $this->assertSame('Europe', $service->continentName($ip));
        $this->assertSame('Europa', $service->continentName($ip, 'de'));
        $this->assertSame('EU', $service->continentCode($ip));

        // Country
        $this->assertSame('United Kingdom', $service->countryName($ip));
        $this->assertSame('Reino Unido', $service->countryName($ip, 'es'));
        $this->assertSame(null, $service->countryName($ip, 'What?'));
        $this->assertSame('GB', $service->countryCode($ip));

        // Region
        $this->assertSame('England', $service->regionName($ip));
        $this->assertSame('ENG', $service->regionCode($ip));

        // City
        $this->assertSame('London', $service->cityName($ip));
        $this->assertSame('Londres', $service->cityName($ip, 'es'));

        // Lat & Lng
        $this->assertSame(51.5142, $service->latitude($ip));
        $this->assertSame(-0.0931, $service->longitude($ip));

        // Timezone
        $this->assertSame('Europe/London', $service->timezone($ip));
    }

    /**
     * @expectedException \NetglueGeoIP\Exception\IpV6UnsupportedException
     */
    public function testIpv6UnsupportedByDatabase()
    {
        $file = 'vendor/maxmind/geoip-test-data/test-data/MaxMind-DB-test-ipv4-24.mmdb';
        $reader = new Reader($file);
        $service = new GeoIPService($reader);
        $service->get('2001::');
    }

    /**
     * @expectedException \NetglueGeoIP\Exception\RuntimeException
     * @expectedExceptionMessage Cannot retrieve data for the given IP
     */
    public function testRetrievalFailure()
    {
        $reader = new Reader('vendor/maxmind/geoip-test-data/test-data//MaxMind-DB-test-broken-pointers-24.mmdb');
        $service = new GeoIPService($reader);
        $service->get('1.1.1.32');
    }

    public function testFactory()
    {
        $reader = $this->prophesize(Reader::class);
        $config = [
            'geoip' => [
                'locales' => [
                    'es',
                    'foo',
                ],
            ],
        ];
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Reader::class)->willReturn($reader->reveal());
        $container->get('config')->willReturn($config);

        $factory = new GeoIPServiceFactory();
        $service = $factory($container->reveal());
        $this->assertInstanceOf(GeoIPService::class, $service);
    }
}
