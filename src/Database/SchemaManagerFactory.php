<?php

namespace Mxc\Shopware\Plugin\Database;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\Factory\FactoryInterface;

class SchemaManagerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var Config $config
         */
        $config = $container->get('config');
        $models = $config->doctrine->models ?? new Config([]);
        $attributes = $config->doctrine->attributes ?? new Config([]);
        $modelManager = $container->get('modelManager');
        $attributeManager = $container->get('attributeManager');
        $logger = $container->get('logger');
        return new SchemaManager(
            $models->toArray(),
            $attributes->toArray(),
            $modelManager,
            $attributeManager,
            $logger);
    }
}