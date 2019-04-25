<?php

namespace Mxc\Shopware\Plugin\Database;

use Mxc\Shopware\Plugin\Service\LoggerAwareInterface;
use Mxc\Shopware\Plugin\Service\LoggerAwareTrait;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareInterface;
use Mxc\Shopware\Plugin\Service\ModelManagerAwareTrait;

class BulkOperation implements LoggerAwareInterface, ModelManagerAwareInterface
{
    use ModelManagerAwareTrait;
    use LoggerAwareTrait;

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