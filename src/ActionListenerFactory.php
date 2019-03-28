<?php

namespace Mxc\Shopware\Plugin;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActionListenerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener =  new $requestedName();
        if (! $listener instanceof ActionListener) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new Exception(sprintf(
                'Expected instance of %s, got %s',
                ActionListener::class,
                is_object($listener) ? get_class($listener) : gettype($listener))
            );
        }
        return $listener;
    }
}