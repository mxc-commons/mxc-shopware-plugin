<?php
/**
 * Created by PhpStorm.
 * User: frank.hein
 * Date: 23.11.2018
 * Time: 20:41
 */

namespace Mxc\Shopware\Plugin;

use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Database\Database;
use Mxc\Shopware\Plugin\Service\LoggerDelegatorFactory;
use Mxc\Shopware\Plugin\Shopware\AttributeManagerFactory;
use Mxc\Shopware\Plugin\Shopware\ConfigurationFactory;
use Mxc\Shopware\Plugin\Shopware\DbalConnectionFactory;
use Mxc\Shopware\Plugin\Shopware\MediaServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ModelManagerFactory;
use Mxc\Shopware\Plugin\Subscriber\EntitySubscriberFactory;
use Mxc\Shopware\Plugin\Subscriber\ModelSubscriber;
use Mxc\Shopware\Plugin\Subscriber\ModelSubscriberFactory;
use Shopware\Components\Plugin as Base;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Throwable;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use Zend\Log\LoggerServiceFactory;
use Zend\ServiceManager\ServiceManager;

class Plugin extends Base
{
    /**
     * @var ServiceManager $services
     */
    protected static $globalServices;

    private static $serviceConfig = [
        'factories' => [
            // shopware service interface
            'dbalConnection'            => DbalConnectionFactory::class,
            'attributeManager'          => AttributeManagerFactory::class,
            'mediaManager'              => MediaServiceFactory::class,
            'modelManager'              => ModelManagerFactory::class,
            'shopwareConfig'            => ConfigurationFactory::class,
            ModelSubscriber::class      => ModelSubscriberFactory::class,

            // services
            Logger::class               => LoggerServiceFactory::class,

        ],
        'delegators' => [
            Logger::class => [
                LoggerDelegatorFactory::class,
            ],
        ],
        'aliases' => [
            'logger' => Logger::class,
        ]
    ];

    /**
     * @return ServiceManager
     */
    public static function getServices() {
        if (self::$globalServices) return self::$globalServices;
        $services = new ServiceManager(self::$serviceConfig);

        // @todo: This is not ok because it relies on a particular composer directory structure
        $config = Factory::fromFile(__DIR__ . '/../../../../Config/plugin.config.php');
        $services->setAllowOverride(true);
        $services->configure($config['services']);
        $services->setService('config', new Config($config));
        $services->setService('events', new EventManager());

        $subscribers = $config['doctrine']['listeners'] ?? [];
        if (count($subscribers) > 0) {
            /**
             * @var \Doctrine\Common\EventManager $evm
             */
            $evm = $services->get('modelManager')->getEventManager();
            $evm->addEventSubscriber($services->get(ModelSubscriber::class));
        }
        $services->get('logger')->info('Subscribers: ' . var_export($subscribers, true));
        foreach ($subscribers as $subscriber => $settings) {
            $model = $settings['model'];
            if (class_exists($model) && class_exists($subscriber)) {
                $services->setFactory($subscriber, EntitySubscriberFactory::class);
                // may move in future to allow lazy instantiation
                $services->get($subscriber);
            }
        }

        $services->setAllowOverride(false);
        self::$globalServices = $services;
        return self::$globalServices;
    }

    /**
     * @param string $function
     * @param ContainerInterface $services
     * @return mixed|EventManagerInterface
     */
    protected static function attachListeners(string $function, ContainerInterface $services) {
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
    public static function trigger(Plugin $plugin, string $function, $param) {
        $services = self::getServices();
        try {
            $services->setAllowOverride(true);
            $services->setService('plugin', $plugin);
            $events = self::attachListeners($function, $services);
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
        self::trigger($this, __FUNCTION__, $context);
    }

    public function uninstall(UninstallContext $context)
    {
        self::trigger($this, __FUNCTION__, $context);
    }

    public function activate(ActivateContext $context)
    {
        self::trigger($this, __FUNCTION__, $context);
    }

    public function deactivate(DeactivateContext $context)
    {
        self::trigger($this, __FUNCTION__, $context);
    }
}
