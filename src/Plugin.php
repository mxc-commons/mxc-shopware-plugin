<?php

namespace Mxc\Shopware\Plugin;

use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Database\Database;
use Mxc\Shopware\Plugin\Service\ServicesTrait;
use Shopware\Components\Plugin as Base;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Throwable;
use Zend\Config\Config;
use Zend\EventManager\EventManagerInterface;

class Plugin extends Base
{
    use ServicesTrait;

    /**
     * @param string $function
     * @param ContainerInterface $services
     * @return mixed|EventManagerInterface
     */
    protected function attachListeners(string $function, ContainerInterface $services) {
        $config = $services->get('config');
        $events = $services->get('events');
        $models = $config->doctrine->models ?? new Config([]);
        $listeners = [];
        if ($models) {
            $listeners[] = $services->get(Database::class);
        }
        $listenerConfig = $config->plugin ?? new Config([]);
        foreach ($listenerConfig as $service => $_) {
            $services->get('logger')->info('Adding listener: ' . $service);
            if (! $services->has($service)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $services->setFactory($service, ActionListenerFactory::class);
            }
            $listeners[] = $services->get($service);
        }
        // execute listeners in reverse order on uninstall and deactivate
        if ($function === 'uninstall' || $function === 'deactivate') {
            $listeners = array_reverse($listeners);
        }
        $priority = count($listeners) * 10 + 1;
        foreach ($listeners as $listener) {
            if (method_exists($listener, $function)) {
                $events->attach($function, [$listener, $function], $priority);
            }
            $priority -= 10;
        }
        return $events;
    }

    /**
     * @param Plugin $plugin
     * @param string $function
     * @param $param
     */
    private function trigger(Plugin $plugin, string $function, $param) {
        $services = $this->getServices();
        try {
            $services->setAllowOverride(true);
            $services->setService('services', $services);
            $services->setService('plugin', $plugin);
            $events = $this->attachListeners($function, $services);
            $services->setAllowOverride(false);
            $events->triggerUntil(
                function ($result) {
                    return $result === false;
                },
                $function,
                null,
                ['context' => $param]
            );
        } catch (Throwable $e) {
            $services->get('logger')->except($e);
        }
    }

    public function install(InstallContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
    }

    public function uninstall(UninstallContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
    }

    public function activate(ActivateContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
    }

    public function deactivate(DeactivateContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
    }
}
