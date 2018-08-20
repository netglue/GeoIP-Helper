<?php
declare(strict_types=1);

namespace NetglueGeoIP;

use MaxMind\Db\Reader;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'geoip' => $this->getGeoIPConfig(),
            'dependencies' => $this->getDependencies(),
            'controller_plugins' => $this->getZendMvcControllerPluginConfig(),
            'proxy_headers' => $this->getProxyHeaderSetup(),
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
            Reader::class => Container\DatabaseReaderFactory::class,
            Helper\ClientIPFromPsrServerRequest::class => Container\Helper\ClientIPFromPsrServerRequestFactory::class,
            Helper\ClientIPFromSuperGlobals::class => Container\Helper\ClientIPFromSuperGlobalsFactory::class,
        ];
    }

    public function getZendMvcControllerPluginConfig() : array
    {
        return [
            'factories' => [
                ZendMvc\Controller\Plugin\GeoIP::class => Container\ZendMvc\Controller\Plugin\GeoIPFactory::class,
            ]
        ];
    }

    public function getProxyHeaderSetup() : array
    {
        return [
            // When figuring out the client IP, should common proxy headers be checked?
            'checkProxyHeaders' => false,
            // If your app is firewalled, and you're sure you can trust that, say,
            // Cloud Flare is sending you the client IP in the header 'CF-Connecting-IP', you can add that here
            // and it will always be used
            'trustedHeader' => null,
            // If your app is on a private network and REMOTE_ADDR is always the load balancer ip, you could say
            // that REMOTE_ADDR is always a trusted proxy
            'remoteAddressIsTrustedProxy' => false,
            // You can provide an array of IP addresses (v4 or v6) of proxies that trust. These will be eliminated as
            // potential client IP addresses
            'trustedProxies' => [],
            // If you provide a non-empty array of proxy headers to inspect, only these headers will be checked,
            // overriding the defaults. If you know that your proxy/loadbalancer only sends X-Forwarded-For, you could
            // put just that one in here:
            'proxyHeadersToInspect' => [],
        ];
    }
}
