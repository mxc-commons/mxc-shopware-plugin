<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\DelegatorFactoryInterface;

class LoggerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Create a new Logger
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param array|null $options
     * @return Logger|object
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $logger = $callback();
        return new Logger($logger);
    }
}