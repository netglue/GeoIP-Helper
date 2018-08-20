<?php
declare(strict_types=1);

namespace NetglueGeoIP\ZendMvc\Controller\Plugin;

use NetglueGeoIP\Helper\ClientIPFromSuperGlobals;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ClientIP extends AbstractPlugin
{

    /** @var ClientIPFromSuperGlobals */
    private $helper;

    /** @var string|null */
    private $ip;

    public function __construct(ClientIPFromSuperGlobals $helper)
    {
        $this->helper = $helper;
        $this->ip = ($this->helper)();
    }

    public function __invoke() :? string
    {
        return $this->ip;
    }
}
