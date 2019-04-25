<?php

namespace Mxc\Shopware\Plugin\Service;

interface LoggerAwareInterface
{
    public function setLog(LoggerInterface $log);
}