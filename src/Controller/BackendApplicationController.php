<?php

namespace Mxc\Shopware\Plugin\Controller;

use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Service\ServicesTrait;
use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Shopware_Controllers_Backend_Application;

class BackendApplicationController extends Shopware_Controllers_Backend_Application
{
    use ServicesTrait;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    /**
     * @var ContainerInterface $services
     */
    protected $services;
    /**
     * @var string $configPath
     */
    protected $configPath;

    public function __construct(
        Enlight_Controller_Request_Request $request,
        Enlight_Controller_Response_Response $response
    ) {
        $this->getServices();
        $this->log = $this->services->get('logger');
        parent::__construct($request, $response);
    }
}