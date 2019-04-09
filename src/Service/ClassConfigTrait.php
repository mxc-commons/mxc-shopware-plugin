<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;
use Zend\Config\Factory;

trait ClassConfigTrait
{
    protected function getClassConfig(ContainerInterface $container, string $class): array
    {
        $config = $container->get('config')['class_config'];
        if (! $config) return [];

        $config = $config[$class];
        if (! $config) return [];

        if (is_string($config)) {
            if (! file_exists($config)) return [];
            return Factory::fromFile($config);
        }

        if (! is_array($config)) return [];

        return $config;
    }
}