<?php
declare(strict_types=1);

namespace NetglueGeoIP\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Exception;
use NetglueGeoIP\Service\GeoIPService;
use NetglueGeoIP\ZendMvc\IpFromRequest;
use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;

class GeoIP extends AbstractPlugin
{

    /** @var GeoIPService */
    private $service;

    /**
     * @var string|null
     */
    private $ip;

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

    public function ip() :? string
    {
        if (! $this->ip) {
            $request = $this->getRequest();
            if ($request) {
                $this->ip = (new IpFromRequest)($request);
            }
        }
        return $this->ip;
    }

    private function getRequest() :? Request
    {
        $controller = $this->getController();
        if (! $controller instanceof InjectApplicationEventInterface) {
            throw new Exception\RuntimeException(\sprintf(
                'This controller does not implement %s so I cannot access the request instance',
                InjectApplicationEventInterface::class
            ));
        }
        $event = $controller->getEvent();
        $request = null;
        if ($event instanceof MvcEvent) {
            $request = $event->getRequest();
        }
        if ($request instanceof Request) {
            return $request;
        }
        return null;
    }

    public function get() :? array
    {
        $ip = $this->ip();
        $data = $ip ? $this->service->get($ip) : null;
        return empty($data) ? null : $data;
    }

    public function countryCode() :? string
    {
        $ip = $this->ip();
        if ($ip) {
            return $this->service->countryCode($ip);
        }
        return null;
    }

    public function countryName() :? string
    {
        $ip = $this->ip();
        if ($ip) {
            return $this->service->countryName($ip);
        }
        return null;
    }

    public function timezone() :? string
    {
        $ip = $this->ip();
        if ($ip) {
            return $this->service->timezone($ip);
        }
        return null;
    }
}
