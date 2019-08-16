<?php

namespace Mxc\Shopware\Plugin\Database;

use Doctrine\ORM\Tools\SchemaTool;
use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Service\ObjectAugmentationTrait;
use Zend\ServiceManager\Factory\FactoryInterface;

class AttributeManagerFactory implements FactoryInterface
{
    use ObjectAugmentationTrait;
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $attributes = $config['doctrine']['attributes'] ?? [];
        $attributeManager = $container->get('attributeCrudService');
        $modelManager = $container->get('modelManager');
        $schemaTool = new SchemaTool($modelManager);
        $metaDataCache = $modelManager->getConfiguration()->getMetadataCacheImpl();
        return $this->augment($container, new AttributeManager(
            $attributes,
            $attributeManager,
            $schemaTool,
            $metaDataCache));
    }
}