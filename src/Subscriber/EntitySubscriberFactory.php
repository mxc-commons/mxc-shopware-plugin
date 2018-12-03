<?php

namespace Mxc\Shopware\Plugin\Subscriber;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\Factory\FactoryInterface;

class EntitySubscriberFactory implements FactoryInterface
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
         * @var EntitySubscriber $subscriber
         */
        $logger = $container->get('logger');
        $events = $container->get('events');
        $subscriber = new $requestedName($events, $logger);
        $config = $container->get('config')->doctrine->listeners->$requestedName ?? new Config([]);
        $events = $container->get(ModelSubscriber::class)->getEventManager();
        $model = $config->model;
        if (null !== $model) {
            foreach ($config->events as $event) {
                $subscriber->attach($events, $model, $event);
            }
        }
        return $subscriber;
    }
}