<?php
declare(strict_types=1);

namespace NetglueGeoIP\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Helper\ClientIPFromSuperGlobals;
use NetglueGeoIP\Service\GeoIPService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class GeoIP extends AbstractPlugin
{

    /** @var GeoIPService */
    private $service;

    /** @var ClientIPFromSuperGlobals */
    private $ipHelper;

    /**
     * @var string|null
     */
    private $ip;

    public function __construct(GeoIPService $service, ?ClientIPFromSuperGlobals $ipHelper = null)
    {
        $this->service = $service;
        $this->ipHelper = $ipHelper ? $ipHelper : new ClientIPFromSuperGlobals();
        $this->ip = ($this->ipHelper)();
    }

    public function __invoke() : self
    {
        return $this;
    }

    public function getService() : GeoIPService
    {
        return $this->service;
    }

    public function ip() :? string
    {
        return $this->ip;
    }

    public function get() :? array
    {
        $data = $this->ip ? $this->service->get($this->ip) : null;
        return empty($data) ? null : $data;
    }

    public function countryCode() :? string
    {
        if ($this->ip) {
            return $this->service->countryCode($this->ip);
        }
        return null;
    }

    public function countryName() :? string
    {
        if ($this->ip) {
            return $this->service->countryName($this->ip);
        }
        return null;
    }

    public function timezone() :? string
    {
        if ($this->ip) {
            return $this->service->timezone($this->ip);
        }
        return null;
    }
}
