<?php

namespace Mxc\Shopware\Plugin\Database;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use Mxc\Shopware\Plugin\ActionListener;
use Mxc\Shopware\Plugin\Service\LoggerAwareInterface;
use Mxc\Shopware\Plugin\Service\LoggerAwareTrait;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareInterface;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareTrait;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Zend\EventManager\EventInterface;

class SchemaManager extends ActionListener implements LoggerAwareInterface, ModelManagerAwareInterface
{
    use LoggerAwareTrait;
    use ModelManagerAwareTrait;

    /**
     * @var CrudService $attributeService
     */
    protected $attributeService;

    /**
     * @var CacheProvider $metaDataCache
     */
    protected $metaDataCache;

    /**
     * @var SchemaTool $schemaTool
     */
    protected $schemaTool;

    /**
     * @var array $attributes
     */
    protected $attributes = [];
    /**
     * @var array $models
     */
    protected $models = [];

    // unique MxcDropShipInnocigs attribute name prefix
    const ATTR_PREFIX = 'mxc_ds_inno_';

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * @param array $models
     * @param array $attributes
     * @param CrudService $attributeService
     */
    public function __construct(
        array $models,
        array $attributes,
        CrudService $attributeService
    ) {
        $this->metaDataCache = $this->modelManager->getConfiguration()->getMetadataCacheImpl();
        $this->schemaTool = new SchemaTool($this->modelManager);
        $this->attributeService = $attributeService;
        $this->models = $models;
        $this->attributes = $attributes;
    }

    /**
     * Add/Update the attributes defined in the §attributes member to the database schema
     */
    protected function updateAttributes() {
        foreach ($this->attributes as $table => $attributes) {
            foreach ($attributes as $name => $config) {
                try {
                    $this->attributeService->update(
                        $table,
                        $name,
                        $config['type'],
                        $config['settings'] ?? [],
                        $config['newColumnName'] ?? null,
                        $config['updateDependingTables'] ?? false,
                        $config['defaultValue'] ?? null);
                } catch (Exception $e) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    throw new Exception('Attribute service failed to create attributes: ' . $e->getMessage());
                }
            }
        }
        $this->updateModel();
    }

    /**
     * Truncate one or more Doctrine managed tables
     * @param array $models
     * @throws DBALException
     */
    public function truncateTable(array $models) {
        $connection = $this->modelManager->getConnection();
        /** @noinspection PhpUnhandledExceptionInspection */
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $dbPlatform = $connection->getDatabasePlatform();
        foreach ($models as $class) {
            $cmd = $this->modelManager->getClassMetadata($class);
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            /** @noinspection PhpUnhandledExceptionInspection */
            $connection->executeUpdate($q);
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Delete the attributes defined in the §attributes member from the database schema
     */
    protected function dropAttributes() {
        foreach ($this->attributes as $table => $attributes) {
            $names = array_keys($attributes);
            foreach ($names as $name) {
                try {
                    $this->attributeService->delete($table, $name);
                } catch (Exception $e) {
                    // ignore attribute did not exist
                }
            }
        }
        $this->updateModel();
        return true;
    }

    protected function updateModel() {
        $this->metaDataCache->deleteAll();
        $this->modelManager->generateAttributeModels(array_keys($this->attributes));
    }

    /**
     * Adds attributes and tables to the database schema
     *
     * @param EventInterface $e
     * @return bool
     * @throws Exception
     */
    public function install(/** @noinspection PhpUnusedParameterInspection */ EventInterface $e) {
        $this->log->enter();
        $this->create();
        $this->log->leave();
        return true;
    }

    public function updateSchema()
    {
        $metaData = [];
        foreach ($this->models as $model) {
            $metaData[] = $this->modelManager->getClassMetadata($model);
        }

        $this->schemaTool->updateSchema(
            $metaData,
            true
        );
    }

    public function create() {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->updateAttributes();
        $this->updateSchema();
    }

    public function drop() {
        $this->schemaTool->dropSchema(
            $this->getDropSchemaMetaData()
        );
        $this->dropAttributes();
    }

    /**
     * Removes attributes and tables from the database schema
     * @param EventInterface $e
     * @return bool
     */
    public function uninstall(EventInterface $e) {
        $this->log->enter();
        $context = $e->getParam('context');

        if (! $context->keepUserData()) {
            $this->drop();
        }

        $this->log->leave();
        return true;
    }

    /**
     * @return array
     */
    protected function getDropSchemaMetaData() {
        if (empty($this->models)) return [];
        $metaData = [];
        $sm = $this->modelManager->getConnection()->getSchemaManager();
        foreach ($this->models as $class) {
            $meta = $this->modelManager->getClassMetadata($class);
            if ($sm->tablesExist([$meta->table['name']])) {
                $metaData[] = $meta;
            }
         }
         return $metaData;
    }
}
