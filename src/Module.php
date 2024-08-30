<?php

declare(strict_types=1);

namespace Lmc\Rbac\Mvc\DevTools;

class Module
{
    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        return [
            'service_manager'         => $configProvider->getDependencies(),
            'view_manager'            => $configProvider->getViewManagerConfig(),
            'laminas-developer-tools' => $configProvider->getLaminasDeveloperToolsConfig(),
        ];
    }
}
