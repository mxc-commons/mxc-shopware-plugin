<?php

namespace Mxc\Shopware\Plugin\Service;

use Zend\ServiceManager\ServiceManager;

trait BootstrapTrait
{
    /**
     * @var ServiceManager $services
     */
    protected $services = null;

    /**
     * @var array $serviceConfig
     */
    private $serviceConfig = [
        'factories' => [
            'services' => ServicesFactory::class,
        ],
    ];

    protected function getServices() {
        if (null === $this->services) {
            $services = new ServiceManager($this->serviceConfig);
            $this->services = $services->get('services');
        }
        return $this->services;
    }
}