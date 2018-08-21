<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Middleware;

use NetglueGeoIP\Container\Middleware\GeolocationFactory;
use NetglueGeoIP\Middleware\Geolocation;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIPTest\TestCase;
use NetglueRealIP\Middleware\IpAddress;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class GeolocationTest extends TestCase
{
    /** @var GeoIPService|ObjectProphecy */
    private $service;

    /** @var ServerRequestInterface */
    private $request;

    /** @var RequestHandlerInterface */
    private $handler;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->prophesize(GeoIPService::class);
        $this->request = ServerRequestFactory::fromGlobals();

        $this->handler = (new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $data = [
                    IpAddress::ATTRIBUTE => $request->getAttribute(IpAddress::ATTRIBUTE),
                    Geolocation::COUNTRY_CODE => $request->getAttribute(Geolocation::COUNTRY_CODE),
                    Geolocation::COUNTRY_NAME => $request->getAttribute(Geolocation::COUNTRY_NAME),
                    Geolocation::TIMEZONE => $request->getAttribute(Geolocation::TIMEZONE),
                    Geolocation::DATA => $request->getAttribute(Geolocation::DATA),
                ];
                return new Response\JsonResponse($data);
            }
        });
    }

    public function getMiddleware() : Geolocation
    {
        return new Geolocation($this->service->reveal());
    }

    public function testMissingIpAddressYieldsNoAttributes()
    {
        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request, $this->handler);
        $data = \json_decode((string) $response->getBody(), true);
        $this->assertNull($data[IpAddress::ATTRIBUTE]);
        $this->assertNull($data[Geolocation::COUNTRY_CODE]);
        $this->assertNull($data[Geolocation::COUNTRY_NAME]);
        $this->assertNull($data[Geolocation::TIMEZONE]);
        $this->assertNull($data[Geolocation::DATA]);
    }

    public function testUnsuccessfulLookup()
    {
        $ip = '1.1.1.1';
        $this->request = $this->request->withAttribute(IpAddress::ATTRIBUTE, $ip);
        $this->service->get($ip)->willReturn([]);
        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request, $this->handler);
        $data = \json_decode((string) $response->getBody(), true);
        $this->assertSame($ip, $data[IpAddress::ATTRIBUTE]);
        $this->assertNull($data[Geolocation::COUNTRY_CODE]);
        $this->assertNull($data[Geolocation::COUNTRY_NAME]);
        $this->assertNull($data[Geolocation::TIMEZONE]);
        $this->assertNull($data[Geolocation::DATA]);
    }

    public function testSuccessfulLookup()
    {
        $ip = '1.1.1.1';
        $this->request = $this->request->withAttribute(IpAddress::ATTRIBUTE, $ip);
        $this->service->get($ip)->willReturn(['data' => 'data']);
        $this->service->countryCode($ip)->willReturn('GB');
        $this->service->countryName($ip)->willReturn('Foo');
        $this->service->timezone($ip)->willReturn('Bar');

        $middleware = $this->getMiddleware();
        $response = $middleware->process($this->request, $this->handler);

        $data = \json_decode((string) $response->getBody(), true);
        $this->assertSame($ip, $data[IpAddress::ATTRIBUTE]);
        $this->assertSame('GB', $data[Geolocation::COUNTRY_CODE]);
        $this->assertSame('Foo', $data[Geolocation::COUNTRY_NAME]);
        $this->assertSame('Bar', $data[Geolocation::TIMEZONE]);
        $this->assertSame(['data' => 'data'], $data[Geolocation::DATA]);
    }

    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(GeoIPService::class)->willReturn($this->service->reveal());

        $factory = new GeolocationFactory();
        $middleware = $factory($container->reveal());
        $this->assertInstanceOf(Geolocation::class, $middleware);
    }
}
