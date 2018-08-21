<?php
declare(strict_types=1);

namespace NetglueGeoIPTest\Command;

use NetglueGeoIP\Command\DownloadCommand;
use NetglueGeoIPTest\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadCommandTest extends TestCase
{
    /** @var Application */
    private $app;

    public function setUp()
    {
        parent::setUp();
        $this->app = new Application('App Name');
    }

    public function addCommand(?array $config = null) : DownloadCommand
    {
        $command = new DownloadCommand($config);
        $this->app->add($command);
        return $command;
    }

    public function testInvalidDirectoryIsError()
    {
        $command = $this->addCommand([
            'databaseDirectory' => './unknown',
        ]);
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('is not a directory', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testBadCityUriIsError()
    {
        $command = $this->addCommand([
            'cityDatabaseUrl' => 'http://unknown.example.com/not-found',
        ]);
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--city' => true,
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Exception occurred attempting to download', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testBadCountryUriIsError()
    {
        $command = $this->addCommand([
            'countryDatabaseUrl' => 'http://unknown.example.com/not-found',
        ]);
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--country' => true,
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Exception occurred attempting to download', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testSuccessfulDownload()
    {
        if (\file_exists(__DIR__ . '/../../../data/GeoLite2-City.mmdb')) {
            $this->markTestSkipped('Skipping - Files are already downloaded');
            return;
        }
        $command = $this->addCommand();
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Downloading files to', $output);
        $this->assertContains('City Database Downloaded', $output);
        $this->assertContains('Country Database Downloaded', $output);
        $this->assertTrue(\file_exists(__DIR__ . '/../../../data/GeoLite2-City.mmdb'));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testNoClobber()
    {
        if (! \file_exists(__DIR__ . '/../../../data/GeoLite2-City.mmdb')) {
            $this->markTestSkipped('Skipping - Files have not yet been downloaded');
            return;
        }
        $command = $this->addCommand();
        $tester = new CommandTester($command);
        $tester->execute([
            'command' => $command->getName(),
            '--no-clobber' => true,
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        ]);
        $output = $tester->getDisplay();
        $this->assertContains('Downloading files to', $output);
        $this->assertContains('Skipped City Database', $output);
        $this->assertContains('Skipped Country Database', $output);
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
