<?php

namespace Mxc\Shopware\Plugin\Controller;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Mxc\Shopware\Plugin\Service\ServicesTrait;
use Shopware_Controllers_Backend_Application;

class BackendApplicationController extends Shopware_Controllers_Backend_Application
{
    use ServicesTrait;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    protected function getLog()
    {
        if (! $this->log) {
            $this->log = $this->getServices()->get('logger');
        }
        return $this->log;
    }
}