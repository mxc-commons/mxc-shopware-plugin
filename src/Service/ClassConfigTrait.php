<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;

trait ClassConfigTrait
{
    protected function getClassConfig(ContainerInterface $container, string $class): array
    {
        $config = $container->get('config')['class_config'];
        if (! $config) return [];

        $config = $config[$class];
        if (! $config) return [];

        if (is_string($config)) {
            $pluginConfigPath = $container->get('config')['plugin_config_path'];
            $configFile = $pluginConfigPath . '/' . $config;
            if (! file_exists($configFile)) return [];
            $config = include $configFile;
        }

        if (! is_array($config)) return [];

        return $config;
    }
}