<?php

namespace LmcRbacMvcDevTools;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'view_manager' => $this->getViewManagerConfig(),
            'laminas-developer-tools' => $this->getLaminasDeveloperToolsConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                \LmcRbacMvc\Collector\RbacCollector::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
            ],
        ];
    }

    public function getViewManagerConfig(): array
    {
        return [
            'template_map' => [
                'laminas-developer-tools/toolbar/lmc-rbac' => __DIR__ . '/../view/laminas-developer-tools/toolbar/lmc-rbac.phtml'
            ]
        ];
    }

    public function getLaminasDeveloperToolsConfig(): array
    {
        return [
            'profiler' => [
                'collectors' => [
                    'lmc_rbac' => \LmcRbacMvc\Collector\RbacCollector::class,
                ],
            ],
            'toolbar' => [
                'entries' => [
                    'lmc_rbac' => 'laminas-developer-tools/toolbar/lmc-rbac',
                ],
            ],
        ];
    }
}
