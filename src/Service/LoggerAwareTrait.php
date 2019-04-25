<?php

namespace Mxc\Shopware\Plugin\Service;

trait LoggerAwareTrait
{
    /** @var LoggerInterface */
    protected $log;

    public function setLog(LoggerInterface $log)
    {
        $this->log = $log;
    }
}