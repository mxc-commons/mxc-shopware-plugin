<?php
/**
 * Created by PhpStorm.
 * User: frank.hein
 * Date: 09.11.2018
 * Time: 19:20
 */

namespace Mxc\Shopware\Plugin\Convenience;

use Throwable;
use Mxc\Shopware\Plugin\Plugin;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;

trait ModelManagerTrait
{
    /**
     * @var ModelManager $modelManager
     *
     */
    private $modelManager;

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function persist(ModelEntity $entity) {
        $this->getModelManager()->persist($entity);
    }

    /**
     * Flush the changes to the Doctrine model mapping an Doctrine exception
     * to our DatabaseException.
     *
     * @throws Throwable
     */
    public function flush() {
        try {
            $this->getModelManager()->flush();
        } catch (Throwable $e) {
            Plugin::getServices()->get('logger')->except($e);
            throw($e);
        }
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function getRepository(string $name) {
        return $this->getModelManager()->getRepository($name);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function createQuery(string $dql) {
        return $this->getModelManager()->createQuery($dql);
    }

    private function getModelManager() {
        if (! $this->modelManager) {
            $this->modelManager = Plugin::getServices()->get('modelManager');
        }
        return $this->modelManager;
    }
}