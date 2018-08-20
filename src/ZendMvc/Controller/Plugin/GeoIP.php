<?php
declare(strict_types=1);

namespace NetglueGeoIP\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Service\GeoIPService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class GeoIP extends AbstractPlugin
{

    /** @var GeoIPService */
    private $service;

    public function __construct(GeoIPService $service)
    {
        $this->service = $service;
    }

    public function __invoke() : self
    {
        return $this;
    }

    public function getService() : GeoIPService
    {
        return $this->service;
    }

    public function get(string $ip) :? array
    {
        return $this->service->get($ip);
    }

    public function countryCode(string $ip) :? string
    {
        return $this->service->countryCode($ip);
    }

    public function countryName(string $ip) :? string
    {
        return $this->service->countryName($ip);
    }

    public function timezone(string $ip) :? string
    {
        return $this->service->timezone($ip);
    }
}
