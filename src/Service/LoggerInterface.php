<?php

namespace Mxc\Shopware\Plugin\Service;

use Throwable;
use Zend\Log\LoggerInterface as BaseInterface;

interface LoggerInterface extends BaseInterface
{
    public function except(Throwable $e, bool $logTrace = true, bool $rethrow = true);

    public function enter();

    public function leave();
}