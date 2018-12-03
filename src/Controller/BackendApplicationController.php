<?php

namespace Mxc\Shopware\Plugin\Controller;

use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Plugin;
use Mxc\Shopware\Plugin\Service\LoggerInterface;
use ReflectionClass;
use Shopware_Controllers_Backend_Application;

class BackendApplicationController extends Shopware_Controllers_Backend_Application
{
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
        $this->services = Plugin::getServices($this->getConfigPath());
        $this->log = $this->services->get('logger');
        parent::__construct($request, $response);
    }

    public function getConfigPath() {
        if (null === $this->configPath) {
            $this->configPath = '';
            $class = strpos(get_class($this), '_Proxies_') > 2 ? get_parent_class($this) : get_class($this);
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflected = new ReflectionClass($class);
            $path = dirname($reflected->getFileName());
            $pattern = 'web' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR;
            $pos = strpos($path, $pattern);
            if ($pos === false) return $this->configPath;
            $pos = strpos($path, DIRECTORY_SEPARATOR, $pos + strlen($pattern));
            if ($pos === false) return $this->configPath;
            $this->configPath = substr($path,0, $pos);
        }
        return $this->configPath;
    }
}