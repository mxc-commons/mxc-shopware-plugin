<?php

namespace Mxc\Shopware\Plugin;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

abstract class ActionListener implements ListenerAggregateInterface {

    use ListenerAggregateTrait;

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        if (method_exists($this, 'install')) {
            $this->listeners[] = $events->attach('install', [$this, 'install'], $priority);
        }
        if (method_exists($this, 'uninstall')) {
            $this->listeners[] = $events->attach('uninstall', [$this, 'uninstall'], $priority);
        }
        if (method_exists($this, 'activate')) {
            $this->listeners[] = $events->attach('activate', [$this, 'activate'], $priority);
        }
        if (method_exists($this, 'deactivate')) {
            $this->listeners[] = $events->attach('deactivate', [$this, 'deactivate'], $priority);
        }
        if (method_exists($this, 'update')) {
            $this->listeners[] = $events->attach('update', [$this, 'update'], $priority);
        }
    }
}