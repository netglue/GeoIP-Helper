<?php
declare(strict_types=1);

namespace NetglueGeoIP\Command;

use NetglueGeoIP\ConfigProvider;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

class DownloadCommand extends ConsoleCommand
{

    /** @var Client */
    private $http;

    /** @var SymfonyStyle */
    private $io;

    /** @var string */
    private $directory;

    /** @var array */
    private $config;

    public function __construct(?array $config = null)
    {
        if (! $config) {
            $config = (new ConfigProvider())()['geoip'];
        }
        $this->config = $config;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('download');
        $this->setDescription('Download a local copy of the GeoIP2 databases');

        $this->addOption(
            'city',
            null,
            InputOption::VALUE_NONE,
            'Download the City Database'
        );

        $this->addOption(
            'country',
            null,
            InputOption::VALUE_NONE,
            'Download the Country Database'
        );

        $this->addOption(
            'all',
            null,
            InputOption::VALUE_NONE,
            'Download both the city and country databases (Default)'
        );

        $this->addOption(
            'no-clobber',
            null,
            InputOption::VALUE_NONE,
            'Don\'t download if the file already exists'
        );

        $this->addArgument(
            'directory',
            InputArgument::OPTIONAL,
            'The directory to store the downloaded databases in'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('directory');
        $defaultDirectory = isset($this->config['databaseDirectory'])
            ? $this->config['databaseDirectory']
            : '.';
        $directory = empty($directory) ? $defaultDirectory : $directory;
        if (! $this->setOutputDirectory($directory)) {
            return 1;
        }
        if ($output->isVerbose()) {
            $this->io->title(\sprintf('Downloading files to %s', $this->directory));
        }

        $city    = $input->getOption('city');
        $country = $input->getOption('country');
        $all     = $input->getOption('all');

        $all     = ($all || (! $country && ! $city));
        $country = ($country || $all);
        $city    = ($city || $all);

        $nc = $input->getOption('no-clobber');

        if ($city) {
            $url  = $this->config['cityDatabaseUrl'];
            $file = $this->resolveTargetFile($url);
            $download = (! \file_exists($file) || ! $nc);
            if ($download) {
                if (! $this->downloadAndExtract($url, $file)) {
                    return 1;
                }
                if ($output->isVerbose()) {
                    $this->io->success('City Database Downloaded');
                }
            } else {
                if ($output->isVerbose()) {
                    $this->io->comment('Skipped City Database - File exists');
                }
            }
        }
        if ($country) {
            $url  = $this->config['countryDatabaseUrl'];
            $file = $this->resolveTargetFile($url);
            $download = (! \file_exists($file) || ! $nc);
            if ($download) {
                if (! $this->downloadAndExtract($url, $file)) {
                    return 1;
                }
                if ($output->isVerbose()) {
                    $this->io->success('Country Database Downloaded');
                }
            } else {
                if ($output->isVerbose()) {
                    $this->io->comment('Skipped Country Database - File exists');
                }
            }
        }
        return 0;
    }

    private function resolveTargetFile(string $url) : string
    {
        return \sprintf(
            '%s/%s.mmdb',
            \rtrim($this->directory, '/'),
            \current(\explode('.', \basename($url)))
        );
    }

    private function setOutputDirectory(string $directory) : bool
    {
        if (! \is_dir($directory)) {
            $this->io->error(\sprintf(
                '%s is not a directory',
                $directory
            ));
            return false;
        }
        $directory = \realpath($directory);
        if (! $directory) {
            $this->io->error('Realpath cannot expand the directory argument');
            return false;
        }
        if (! \is_writable($directory)) {
            $this->io->error(\sprintf(
                '%s cannot be written to',
                $directory
            ));
            return false;
        }
        $this->directory = $directory;
        return true;
    }

    private function downloadAndExtract(string $url, string $targetFile) : bool
    {

        $response = $this->download($url);
        if (! $response) {
            return false;
        }
        $bytes = \file_put_contents($targetFile, \gzdecode($response->getBody()));
        if ($bytes === false) {
            $this->io->error('Failed to deflate or write the database file');
            return false;
        }
        return true;
    }

    private function download(string $uri) :? Response
    {
        try {
            $client = $this->getHttpClient();
            $client->setUri($uri);
            $client->setMethod(Request::METHOD_GET);
            $response = $client->send();
            if (! $response->isSuccess()) {
                $this->io->error(\sprintf(
                    'Failed to download %s. Response code: %d',
                    $uri,
                    $response->getStatusCode()
                ));
                return null;
            }
            return $response;
        } catch (Throwable $exception) {
            $this->io->error(\sprintf(
                'Exception occurred attempting to download %s - %s',
                $uri,
                $exception->getMessage()
            ));
            return null;
        }
    }

    private function getHttpClient() : Client
    {
        if (! $this->http instanceof Client) {
            $this->http = new Client(null, ['useragent' => 'Netglue GeoIP Library']);
        }
        return $this->http;
    }
}
