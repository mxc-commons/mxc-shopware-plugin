<?php

namespace Mxc\Shopware\Plugin\Service;

use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Database\AttributeManager;
use Mxc\Shopware\Plugin\Database\BulkOperation;
use Mxc\Shopware\Plugin\Database\SchemaManager;
use Mxc\Shopware\Plugin\Shopware\AuthServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ConfigurationFactory;
use Mxc\Shopware\Plugin\Shopware\CrudServiceFactory;
use Mxc\Shopware\Plugin\Shopware\DbalConnectionFactory;
use Mxc\Shopware\Plugin\Shopware\MediaServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ModelManagerFactory;
use Zend\Config\Factory;
use Zend\EventManager\EventManager;
use Zend\Filter\StringToLower;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Log\Formatter\Simple;
use Zend\Log\Logger;
use Zend\Log\LoggerServiceFactory;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

class ServicesFactory implements FactoryInterface
{
    protected $pluginName;
    protected $pluginConfig;

    private $serviceConfig = [
        'factories' => [
            // shopware service interface
            'dbalConnection'            => DbalConnectionFactory::class,
            'attributeCrudService'      => CrudServiceFactory::class,
            'mediaManager'              => MediaServiceFactory::class,
            'modelManager'              => ModelManagerFactory::class,
            'shopwareConfig'            => ConfigurationFactory::class,
            'authService'               => AuthServiceFactory::class,

            // services
            Logger::class               => LoggerServiceFactory::class,
            BulkOperation::class        => AugmentedObjectFactory::class,

        ],
        'magicals' => [
            SchemaManager::class,
            AttributeManager::class,
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
        return ($toLowerCase($toUnderScore($pluginClass)));
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

    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $services = new ServiceManager($this->serviceConfig);
        $this->pluginName = $options['pluginName'];
        $this->pluginConfig = $options['pluginConfig'];
        $config = file_exists($this->pluginConfig) ? Factory::fromFile($this->pluginConfig) : [];
        if (! isset($config['log'])) {
            $config['log'] = $this->getLoggerConfig();
        }
        $services->setAllowOverride(true);
        $services->configure($config['services'] ?? []);
        $services->setService('config', $config);
        $services->setService('events', new EventManager());
        $services->setService('services', $services);
        $services->setAllowOverride(false);

        return $services;
    }
}