<?php

namespace LmcRbac\Mvc\DevTools;

class Module
{

    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        return [
            'service_manager' => $configProvider->getDependencies(),
            'view_manager' => $configProvider->getViewManagerConfig(),
            'laminas-developer-tools' => $configProvider->getLaminasDeveloperToolsConfig(),
        ];
    }
}
