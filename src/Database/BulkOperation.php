<?php

namespace Mxc\Shopware\Plugin\Database;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Shopware\Components\Model\ModelManager;

class BulkOperation
{
    /**
     * @var LoggerInterface $log
     */
    protected $log;
    /**
     * @var ModelManager $modelManager
     */
    protected $modelManager;

    /**
     * BulkOperation constructor.
     *
     * @param ModelManager $modelManager
     * @param LoggerInterface $log
     */
    public function __construct(ModelManager $modelManager, LoggerInterface $log)
    {
        $this->modelManager = $modelManager;
        $this->log = $log;
    }

    public function update(array $filter)
    {
        $alias = 'e';
        $builder = $this->modelManager->createQueryBuilder()
            ->update($filter['entity'], $alias);

        $i = 0;
        foreach ($filter['andWhere'] as $criteria) {
            /**
             * Variables created by extract
             *
             * @var $field
             * @var $operator
             * @var $value
             */
            $i += 1;
            extract($criteria);
            $key = $this->getAliasedKey($alias, $field);
            $builder->andWhere("$key $operator ?$i");
            $builder->setParameter($i, $value);
        }
        foreach ($filter['set'] as $key => $value) {
            $i += 1;
            $key = $this->getAliasedKey($alias, $key);
            $builder->set($key, "?$i");
            $builder->setParameter($i, $value);
        }
        $builder->getQuery()->execute();
    }

    protected function getAliasedKey(string $alias, string $key)
    {
        return $alias . '.' . $key;
    }

}