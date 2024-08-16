<?php

namespace LmcRbac\Mvc\DevToolsTest;

use LmcRbac\Mvc\DevTools\ConfigProvider;
use LmcRbac\Mvc\DevTools\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LmcRbac\Mvc\DevTools\Module
 */
class ModuleTest extends TestCase
{
    public function testModule(): void
    {
        $provider = new ConfigProvider();
        $module = new Module();
        $expected = [
            'service_manager' => $provider->getDependencies(),
            'view_manager' => $provider->getViewManagerConfig(),
            'laminas-developer-tools' => $provider->getLaminasDeveloperToolsConfig(),
        ];
        $this->assertEquals($expected, $module->getConfig());
    }
}
