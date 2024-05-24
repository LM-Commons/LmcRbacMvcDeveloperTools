<?php

namespace LmcRbacMvcDevTools;

class Module implements \Laminas\ModuleManager\Feature\ConfigProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $configProvider = new ConfigProvider();
        return [
            'service_manager' => $configProvider->getDependencies(),
            'view_manager' => $configProvider->getViewManagerConfig(),
            'laminas-developer-tools' => $configProvider->getLaminasDeveloperToolsConfig(),
        ];
    }
}
