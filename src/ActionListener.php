<?php

namespace Mxc\Shopware\Plugin;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Zend\Config\Config;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

abstract class ActionListener implements ListenerAggregateInterface {

    use ListenerAggregateTrait;
    /**
     * @var Config $config
     */
    protected $config;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    public function __construct(Config $config, LoggerInterface $log) {
        $this->config = $config;
        $this->log = $log;
    }

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