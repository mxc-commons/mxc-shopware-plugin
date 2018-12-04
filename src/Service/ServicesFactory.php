<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Shopware\AttributeManagerFactory;
use Mxc\Shopware\Plugin\Shopware\ConfigurationFactory;
use Mxc\Shopware\Plugin\Shopware\DbalConnectionFactory;
use Mxc\Shopware\Plugin\Shopware\MediaServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ModelManagerFactory;
use Mxc\Shopware\Plugin\Subscriber\EntitySubscriberFactory;
use Mxc\Shopware\Plugin\Subscriber\ModelSubscriber;
use Mxc\Shopware\Plugin\Subscriber\ModelSubscriberFactory;
use ReflectionClass;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\EventManager\EventManager;
use Zend\Filter\StringToLower;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Log\Logger;
use Zend\Log\Formatter\Simple;
use Zend\Log\LoggerServiceFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

class ServicesFactory implements FactoryInterface
{
    /**
     * @var string $configPath
     */
    protected $configPath;
    /**
     * @var string $pluginName
     */
    protected $pluginName;

    private $serviceConfig = [
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

    protected function getLogFileName(string $pluginClass) {
        $toUnderScore = new CamelCaseToUnderscore();
        $toLowerCase = new StringToLower();
        return ($toLowerCase->filter($toUnderScore->filter($pluginClass)));
    }

    protected function getLoggerConfig() {
        return [
            'writers' => [
                'stream' => [
                    'name' => 'stream',
                    'priority'  => Logger::ALERT,
                    'options'   => [
                        'stream'    => Shopware()->DocPath() . 'var/log/' . $this->getLogFileName($this->pluginName) . '-' . date('Y-m-d') . '.log',
                        'formatter' => [
                            'name'      => Simple::class,
                            'options'   => [
                                'format'            => '%timestamp% %priorityName%: %message% %extra%',
                                'dateTimeFormat'    => 'H:i:s',
                            ],
                        ],
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => Logger::DEBUG,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getConfigPath() {
        if (null === $this->configPath) {
            $this->configPath = '';
            $class = strpos(get_class($this), '_Proxies_') > 2 ? get_parent_class($this) : get_class($this);
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflected = new ReflectionClass($class);
            $path = dirname($reflected->getFileName());
            $pattern = Shopware()->DocPath() . 'custom/plugins/';
            $pos = strpos($path, $pattern);
            if ($pos !== 0) return $this->configPath; // error
            $pLen = strlen($pattern);
            $pos = strpos($path, DIRECTORY_SEPARATOR, $pLen);
            if ($pos === false) return $this->configPath; // error
            $this->pluginName = substr($path, $pLen, $pos - $pLen);
            $this->configPath = substr($path,0, $pos);
        }
        return $this->configPath;
    }

    /**
     * Create an object
     *
     * @param  \Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $services = new ServiceManager($this->serviceConfig);
        $path = $this->getConfigPath() . '/Config/plugin.config.php';
        $config = Factory::fromFile($path);
        if (! isset($config['log'])) {
            $config['log'] = $this->getLoggerConfig();
        }
        $services->setAllowOverride(true);
        $services->configure($config['services']);
        $services->setService('config', new Config($config));
        $services->setService('events', new EventManager());
        $services->setService('services', $services);
        $log = $services->get('logger');

        $subscribers = $config['doctrine']['listeners'] ?? [];
        if (count($subscribers) > 0) {
            /**
             * @var \Doctrine\Common\EventManager $evm
             */
            $evm = $services->get('modelManager')->getEventManager();
            $evm->addEventSubscriber($services->get(ModelSubscriber::class));
            $log->info('ModelSubscriber was added');
        }
        foreach ($subscribers as $subscriber => $settings) {
            $model = $settings['model'];
            if (class_exists($model) && class_exists($subscriber)) {
                if (! $services->has($subscriber)) {
                    $services->setFactory($subscriber, EntitySubscriberFactory::class);
                }
                // may move in future to allow lazy instantiation
                $services->get($subscriber);
                $log->info('Model Listener ' . $subscriber . ' added.');
            }
        }
        $services->setAllowOverride(false);
        return $services;
    }
}