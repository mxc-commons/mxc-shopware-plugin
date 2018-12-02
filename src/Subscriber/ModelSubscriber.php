<?php

namespace Mxc\Shopware\Plugin\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Proxy\Proxy;
use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\ResponseCollection;

class ModelSubscriber extends EventNameProvider implements EventSubscriber
{
    use EventManagerAwareTrait;

    protected $config;
    protected $eventMap = null;
    protected $log;

    public function __construct(Config $config, LoggerInterface $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function getSubscribedEvents()
    {
        $events = array_keys($this->getEventMap());
        return $events;
    }

    public function preRemove(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }
    public function postRemove(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function prePersist(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function postPersist(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->trigger(__FUNCTION__, $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->trigger(__FUNCTION__, $args);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->trigger(__FUNCTION__, $args);
    }

    public function loadClassMetaData(LifecycleEventArgs $args)
    {
        $this->trigger(__FUNCTION__, $args);
    }

    public function onClassMetaDataNotFound(LifecycleEventArgs $args)
    {
        $this->trigger(__FUNCTION__, $args);
    }

    public function preFlush(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function onFlush(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function postFlush(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    public function onClear(LifecycleEventArgs $args) {
        $this->trigger(__FUNCTION__, $args);
    }

    protected function getEntityClass($entity)
    {
        return ($entity instanceof Proxy) ? get_parent_class($entity) : get_class($entity);
    }

    protected function getEventMap() {
        if (null !== $this->eventMap) return $this->eventMap;
        $this->eventMap = [];
        foreach ($this->config as $settings) {
            $model = $settings->model;
            $events = $settings->events ?? [];
            foreach ($events as $event) {
                $this->eventMap[$event][$model] = true;
            }
        }
        return $this->eventMap;
    }

    protected function trigger(string $event, $args) {
        /**
         * @var LifecycleEventArgs $args
         */
        $entityClass = $this->getEntityClass($args->getEntity());
        if ( ! isset($this->getEventMap()[$event][$entityClass])) return null;

        $this->log->info('Triggering listener for ' . $entityClass);

        /**
         * @var ResponseCollection $result
         */
        $result = $this->getEventManager()->triggerUntil(
            function($result) {
                return $result === true;
            },
            $this->getEventName($entityClass, $event),
            null,
            [ 'args' => $args ]
        );
        return $result;
    }
}
