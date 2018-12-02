<?php
/**
 * Created by PhpStorm.
 * User: frank.hein
 * Date: 27.11.2018
 * Time: 16:44
 */

namespace Mxc\Shopware\Plugin\Subscriber;

class EventNameProvider
{
    protected $delimiter = '::';

    public function getEventName(string $class, string $event) {
        return $class . $this->delimiter . $event;
    }

    public function explodeEventName(string $eventName) {
        return explode($this->delimiter, $eventName);
    }
}