<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\DevToolsTest;

use Lmc\Rbac\Mvc\DevTools\ConfigProvider;
use Lmc\Rbac\Mvc\DevTools\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\DevTools\Module
 */
class ModuleTest extends TestCase
{
    public function testModule(): void
    {
        $provider = new ConfigProvider();
        $module   = new Module();
        $expected = [
            'service_manager'         => $provider->getDependencies(),
            'view_manager'            => $provider->getViewManagerConfig(),
            'laminas-developer-tools' => $provider->getLaminasDeveloperToolsConfig(),
        ];
        $this->assertEquals($expected, $module->getConfig());
    }
}
