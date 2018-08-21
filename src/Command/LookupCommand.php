<?php
declare(strict_types=1);

namespace NetglueGeoIP\Command;

use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIP\Exception;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LookupCommand extends ConsoleCommand
{
    /** @var GeoIPService */
    private $geoIpService;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(GeoIPService $geoIPService)
    {
        $this->geoIpService = $geoIPService;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('geoip:lookup');
        $this->setDescription('Get information about an IP address');

        $this->addArgument(
            'ip',
            InputArgument::REQUIRED,
            'The IP address to lookup'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $ip = $input->getArgument('ip');
        $filterFlags = \FILTER_FLAG_IPV4 | \FILTER_FLAG_IPV6;
        if (! \filter_var($ip, \FILTER_VALIDATE_IP, $filterFlags)) {
            $this->io->error(\sprintf('%s is not a valid IP address', $ip));
            return 1;
        }
        return $this->displayResult($ip);
    }

    private function displayResult(string $ip) : int
    {
        try {
            $data = $this->geoIpService->get($ip);
        } catch (Exception\ExceptionInterface $exception) {
            $this->io->error(sprintf(
                'Failed to locate info for the ip address %s: %s',
                $ip,
                $exception->getMessage()
            ));
            return 1;
        }
        $this->io->title(\sprintf(
            'Information for %s',
            $ip
        ));
        $country = $this->geoIpService->countryName($ip);
        if ($country) {
            $this->io->success(\sprintf(
                'Country: %s (%s)',
                $country,
                $this->geoIpService->countryCode($ip)
            ));
        }
        $city = $this->geoIpService->cityName($ip);
        if ($city) {
            $this->io->success(\sprintf(
                'City: %s',
                $city
            ));
        }
        $time = $this->geoIpService->timezone($ip);
        if ($time) {
            $this->io->success(\sprintf(
                'TimeZone: %s',
                $time
            ));
        }
        return 0;
    }
}
