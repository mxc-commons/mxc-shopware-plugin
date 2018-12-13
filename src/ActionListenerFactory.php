<?php

namespace Mxc\Shopware\Plugin;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\Factory\FactoryInterface;
use Exception;

class ActionListenerFactory implements FactoryInterface
{
    protected function createObject(ContainerInterface $container, $requestedName, array $options = null) {
        $config = $container->get('config')->plugin->$requestedName ?? new Config([]);
        $logger = $container->get('logger');
        return new $requestedName($config, $logger);
    }

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
        $events = $container->get('events');
        /**
         * @var ActionListener $listener
         */
        $listener = $this->createObject($container, $requestedName, $options);
        if (! $listener instanceof ActionListener) {
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new Exception(sprintf(
                'Expected instance of %s, got %s',
                ActionListener::class,
                is_object($listener) ? get_class($listener) : gettype($listener))
            );
        }
        $listener->attach($events);
        return $listener;
    }
}