<?php

namespace Mxc\Shopware\Plugin;

use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Database\AttributeManager;
use Mxc\Shopware\Plugin\Database\SchemaManager;
use Shopware\Components\Plugin as Base;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Throwable;
use Zend\EventManager\EventManagerInterface;

class Plugin extends Base
{
    protected $installClearCache;
    protected $uninstallClearCache;
    protected $activateClearCache;
    protected $deactivateClearCache;
    protected $updateClearCache;

    /**
     * @param string $function
     * @param ContainerInterface $services
     * @return mixed|EventManagerInterface
     */
    private function attachListeners(string $function, ContainerInterface $services) {
        $config = $services->get('config');
        $events = $services->get('events');
        $listeners = is_array($config['doctrine']['models']) ? [SchemaManager::class]: [];
        if (is_array($config['doctrine']['attributes'])) {
            $listeners[] = AttributeManager::class;
        }
        $addlListeners = $config['plugin'] ?? [];
        foreach ($addlListeners as $listener) {
            $listeners[] = $listener;
        }
        // attach listeners in reverse order on uninstall and deactivate
        if ($function === 'uninstall' || $function === 'deactivate') {
            $listeners = array_reverse($listeners);
        }
        foreach ($listeners as $service) {
            if (! $services->has($service)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $services->setFactory($service, ActionListenerFactory::class);
            }
            $services->get($service)->attach($events);
        }
        return $events;
    }

    /**
     * @param Plugin $plugin
     * @param string $function
     * @param $param
     */
    private function trigger(Plugin $plugin, string $function, $param) {
        $services = $plugin->getServices();
        try {
            $services->setAllowOverride(true);
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
        if ($this->installClearCache !== null) {
            $context->scheduleClearCache($this->installClearCache);
        }
    }

    public function uninstall(UninstallContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
        if ($this->uninstallClearCache !== null) {
            $context->scheduleClearCache($this->uninstallClearCache);
        }
    }

    public function update(UpdateContext $context) {
        $this->trigger($this, __FUNCTION__, $context);
        if ($this->updateClearCache !== null) {
            $context->scheduleClearCache($this->updateClearCache);
        }
    }

    public function activate(ActivateContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
        if ($this->activateClearCache !== null) {
            $context->scheduleClearCache($this->activateClearCache);
        }
    }

    public function deactivate(DeactivateContext $context)
    {
        $this->trigger($this, __FUNCTION__, $context);
        if ($this->deactivateClearCache !== null) {
            $context->scheduleClearCache($this->deactivateClearCache);
        }
    }
}
