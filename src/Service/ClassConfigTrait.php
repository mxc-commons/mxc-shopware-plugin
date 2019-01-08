<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;

trait ClassConfigTrait
{
    protected function getClassConfig(ContainerInterface $container, string $class)
    {
        $config = $container->get('config');
        if ($config->class_config) {
            return $config->class_config->$class;
        }
        return new Config([]);
    }
}