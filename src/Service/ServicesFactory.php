<?php /** @noinspection PhpIncludeInspection */

namespace Mxc\Shopware\Plugin\Service;

use Mxc\Shopware\Plugin\Database\AttributeManager;
use Mxc\Shopware\Plugin\Database\BulkOperation;
use Mxc\Shopware\Plugin\Database\SchemaManager;
use Mxc\Shopware\Plugin\Shopware\AuthServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ConfigurationFactory;
use Mxc\Shopware\Plugin\Shopware\CrudServiceFactory;
use Mxc\Shopware\Plugin\Shopware\DbalConnectionFactory;
use Mxc\Shopware\Plugin\Shopware\MediaServiceFactory;
use Mxc\Shopware\Plugin\Shopware\ModelManagerFactory;
use Mxc\Shopware\Plugin\Utility\StringUtility;
use Zend\Log\Formatter\Simple;
use Zend\Log\Logger;
use Zend\Log\LoggerServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ServicesFactory
{
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
        return strtolower(StringUtility::camelCaseToUnderscore($pluginClass));
    }

    protected function getLoggerConfig(string $pluginName) {
        return [
            'writers' => [
                'stream' => [
                    'name' => 'stream',
                    'priority'  => Logger::ALERT,
                    'options'   => [
                        'stream'    => Shopware()->DocPath() . 'var/log/' . $this->getLogFileName($pluginName) . '-' . date('Y-m-d') . '.log',
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

    public function getServices(string $pluginDir) {
        $pluginName = substr(strrchr($pluginDir, '/'), 1);
        $configDir = $pluginDir . '/Config';
        $configFile = $configDir . '/plugin.config.php';
        $services = new ServiceManager($this->serviceConfig);
        $config = [];
        if (file_exists($configFile)) {
            $config = include $configFile;
        }
        if (! isset($config['log'])) {
            $config['log'] = $this->getLoggerConfig($pluginName);
        }
        $services->setAllowOverride(true);
        $services->configure($config['services'] ?? []);
        $config['plugin_config_path'] = $configDir;
        $services->setService('config', $config);
        $services->setService('services', $services);
        $services->setAllowOverride(false);

        return $services;

    }
}