<?php /** @noinspection PhpUnusedParameterInspection */

namespace Mxc\Shopware\Plugin\Subscriber;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

class EntitySubscriber extends EventNameProvider {
    /**
     * @var LoggerInterface $log
     */
    protected $log;
    /**
     * @var EventManagerInterface $events
     */
    protected $events;

    public function __construct(EventManagerInterface $events, LoggerInterface $log) {
        $this->log = $log;
        $this->events = $events;
    }

    public function attach(EventManagerInterface $events, string $class, string $event, int $priority = 1) {
        $events->attach($this->getEventName($class, $event), [$this, $event], $priority);
    }

    protected function notImplemented($event) {
        $this->log->warn(get_class($this). ' does not implement a handler for ' . $event);
    }

    public function preRemove(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function postRemove(EventInterface $e){
        $this->notImplemented(__FUNCTION__);
    }
    public function prePersist(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function postPersist(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function preUpdate(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function postUpdate(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function postLoad(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function loadClassMetaData(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function onClassMetaDataNotFound(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function preFlush(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function onFlush(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
    public function postFlush(EventInterface $e) {
        $this->notImplemented(__FUNCTION__);
    }
}