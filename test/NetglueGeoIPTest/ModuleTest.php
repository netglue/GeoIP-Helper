<?php
declare(strict_types=1);

namespace NetglueGeoIPTest;

use NetglueGeoIP\Module;

class ModuleTest extends TestCase
{
    public function testBasic()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getControllerPluginConfig());
        $this->assertInternalType('array', $module->getConfig());
        $this->assertInternalType('array', $module->getServiceConfig());
    }
}
