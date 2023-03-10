<?php

namespace LmcRbacMvcDevTools;

class Module implements \Laminas\ModuleManager\Feature\ConfigProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
