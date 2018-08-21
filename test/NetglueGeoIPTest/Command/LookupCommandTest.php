<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Command;

use MaxMind\Db\Reader;
use NetglueGeoIP\Command\LookupCommand;
use NetglueGeoIP\Container\Command\LookupCommandFactory;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIPTest\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LookupCommandTest extends TestCase
{
    /** @var Reader */
    private $reader;

    /** @var GeoIPService */
    private $service;

    /** @var Application */
    private $app;

    private $ip = '81.2.69.160';

    public function setUp()
    {
        $this->reader = new Reader('vendor/maxmind/geoip-test-data/test-data/GeoIP2-City-Test.mmdb');
        $this->service = new GeoIPService($this->reader);
        $this->app = new Application('App Name');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->reader->close();
        parent::tearDown();
    }

    public function addCommand() : LookupCommand
    {
        $command = new LookupCommand($this->service);
        $this->app->add($command);
        return $command;
    }

    public function testInvalidIpIsError()
    {
        $command = $this->addCommand();
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'ip' => 'foo',
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('is not a valid IP address', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testSuccessfulLookup()
    {
        $command = $this->addCommand();
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'ip' => $this->ip,
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Country: United Kingdom', $output);
        $this->assertContains('City: London', $output);
        $this->assertContains('TimeZone: Europe/London', $output);
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testUnsuccessfulLookup()
    {
        $this->reader = new Reader('vendor/maxmind/geoip-test-data/test-data//MaxMind-DB-test-broken-pointers-24.mmdb');
        $this->service = new GeoIPService($this->reader);
        $command = $this->addCommand();
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            'ip' => '1.1.1.32',
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Failed to locate info for the ip address', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testFactory()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(GeoIPService::class)->willReturn($this->service);

        $factory = new LookupCommandFactory();
        $command = $factory($container->reveal());
        $this->assertInstanceOf(LookupCommand::class, $command);
    }
}
