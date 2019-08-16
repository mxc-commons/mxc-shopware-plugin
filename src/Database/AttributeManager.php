<?php

namespace Mxc\Shopware\Plugin\Database;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;
use Mxc\Shopware\Plugin\ActionListener;
use Mxc\Shopware\Plugin\Service\LoggerAwareInterface;
use Mxc\Shopware\Plugin\Service\LoggerAwareTrait;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareInterface;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareTrait;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Zend\EventManager\EventInterface;

class AttributeManager extends ActionListener implements LoggerAwareInterface, ModelManagerAwareInterface
{
    use LoggerAwareTrait;
    use ModelManagerAwareTrait;

    /**
     * @var CrudService $attributeService
     */
    private $attributeService;

    /**
     * @var CacheProvider $metaDataCache
     */
    private $metaDataCache;

    /**
     * @var SchemaTool $schemaTool
     */
    private $schemaTool;

    /**
     * @var array $attributes
     */
    protected $attributes = [];
    /**
     * @var array $models
     */
    protected $models = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * @param array $attributes
     * @param CrudService $attributeService
     * @param SchemaTool $schemaTool
     * @param CacheProvider $metaDataCache
     */
    public function __construct(
        array $attributes,
        CrudService $attributeService,
        SchemaTool $schemaTool,
        CacheProvider $metaDataCache
    ) {
        $this->metaDataCache = $metaDataCache;
        $this->schemaTool = $schemaTool;
        $this->attributeService = $attributeService;
        $this->attributes = $attributes;
    }
    /**
     * Adds attributes and tables to the database schema
     *
     * @param EventInterface $e
     * @return bool
     * @throws Exception
     */
    public function install(/** @noinspection PhpUnusedParameterInspection */ EventInterface $e) {
        $this->create();
        return true;
    }

    /**
     * Removes attributes and tables from the database schema
     * @param EventInterface $e
     * @return bool
     */
    public function uninstall(EventInterface $e) {
        $context = $e->getParam('context');
        if (! $context->keepUserData()) $this->drop();
        return true;
    }

    public function create() {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->updateAttributes();
    }

    public function drop() {
        $this->dropAttributes();
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


    /**
     * Add/Update the attributes defined in the §attributes member to the database schema
     *
     *  'settings' => [
     *      'label'            => '',
     *      'supportText'      => '',
     *      'helpText'         => '',
     *      'translatable'     => false,
     *      'displayInBackend' => false,
     *      'position'         => 10000,
     *      'custom'           => false
     *   ]
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

    private function updateModel() {
        $this->metaDataCache->deleteAll();
        $this->modelManager->generateAttributeModels(array_keys($this->attributes));
    }
}