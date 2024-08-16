<?php

namespace LmcRbac\Mvc\DevToolsTest;

use LmcRbac\Mvc\DevTools\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LmcRbac\Mvc\DevTools\ConfigProvider
 */
class ConfigProviderTest extends TestCase
{
    public function testProvidesExpectedConfig()
    {
        $provider = new ConfigProvider();
        $expectedDependencyConfig = [
            'factories' => [
                \LmcRbac\Mvc\DevTools\Collector\RbacCollector::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
            ],
        ];
        $expectedLaminasDevtoolsConfig = [
            'profiler' => [
                'collectors' => [
                    'lmc_rbac' => \LmcRbac\Mvc\DevTools\Collector\RbacCollector::class,
                ],
            ],
            'toolbar' => [
                'entries' => [
                    'lmc_rbac' => 'laminas-developer-tools/toolbar/lmc-rbac',
                ],
            ],
        ];
        $expectedViewManagerConfig = [
            'template_map' => [
                'laminas-developer-tools/toolbar/lmc-rbac' => realpath(__DIR__ . '/../view/laminas-developer-tools/toolbar/lmc-rbac.phtml'),
            ],
        ];
        $this->assertEquals($expectedDependencyConfig, $provider->getDependencies());
        $this->assertEquals($expectedLaminasDevtoolsConfig, $provider->getLaminasDeveloperToolsConfig());
        // View Manager config
        $expectedViewManagerConfig = [
            'template_map' => [
                'laminas-developer-tools/toolbar/lmc-rbac' => realpath(__DIR__ . '/../view/laminas-developer-tools/toolbar/lmc-rbac.phtml'),
            ],
        ];
        $result = $provider->getViewManagerConfig();
        // substitute path
        $result['template_map']['laminas-developer-tools/toolbar/lmc-rbac'] = realpath($result['template_map']['laminas-developer-tools/toolbar/lmc-rbac']);
        $this->assertEquals($expectedViewManagerConfig, $result);

        $expectedConfig = [
            'dependencies' => $expectedDependencyConfig,
            'view_manager' => $expectedViewManagerConfig,
            'laminas-developer-tools' => $expectedLaminasDevtoolsConfig,
        ];

        $result = $provider();
        // substitute path
        $result['view_manager']['template_map']['laminas-developer-tools/toolbar/lmc-rbac'] = realpath($result['view_manager']['template_map']['laminas-developer-tools/toolbar/lmc-rbac']);
        $this->assertEquals($expectedConfig, $result);
    }
}
