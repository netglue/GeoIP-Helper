<?php
declare(strict_types=1);

namespace NetglueGeoIP;

use MaxMind\Db\Reader;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'geoip'              => $this->getGeoIPConfig(),
            'dependencies'       => $this->getDependencies(),
            'controller_plugins' => $this->getZendMvcControllerPluginConfig(),
        ];
    }

    public function getGeoIPConfig() : array
    {
        return [
            //
            // Choose which database to use and the on-disk location.
            // The country database is smaller and quicker but obviously lacks more detailed information.
            //
            // The reason you have to specify the path for the file is that we don't assume you are using the
            // download utility to get a copy of the database, therefore, it could be in a system-wide location
            //
            'databaseFile' => '/usr/local/var/GeoIP/GeoLite2-Country.mmdb',
            //'databaseFile' => '/usr/local/var/GeoIP/GeoLite2-City.mmdb',

            //
            // Locales in order of preference. These must correspond to those used by GeoIP
            //
            'locales' => [
                'en'
            ],

            //
            // These URLs are used by the download utility to get a copy of the free version of the city and country
            // databases. They are effectively hardcoded when used with the bin/geoip utility, but can be overridden
            // if you create a Command\DownloadCommand with your DI container and pass it a modified configuration.
            //
            'cityDatabaseUrl'    => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz',
            'countryDatabaseUrl' => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz',
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories' => [
                Reader::class => Container\DatabaseReaderFactory::class,
            ],
        ];
    }

    public function getZendMvcControllerPluginConfig() : array
    {
        return [
            'factories' => [
                ZendMvc\Controller\Plugin\GeoIP::class => Container\ZendMvc\Controller\Plugin\GeoIPFactory::class,
            ],
            'aliases' => [
                'geoIp' => ZendMvc\Controller\Plugin\GeoIP::class,
            ],
        ];
    }
}
