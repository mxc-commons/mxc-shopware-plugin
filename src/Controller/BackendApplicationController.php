<?php

namespace Mxc\Shopware\Plugin\Controller;

use Doctrine\ORM\QueryBuilder;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Interop\Container\ContainerInterface;
use Mxc\Shopware\Plugin\Service\ServicesTrait;
use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Shopware_Controllers_Backend_Application;

class BackendApplicationController extends Shopware_Controllers_Backend_Application
{
    use ServicesTrait;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    /**
     * @var ContainerInterface $services
     */
    protected $services;
    /**
     * @var string $configPath
     */
    protected $configPath;

    public function __construct(
        Enlight_Controller_Request_Request $request,
        Enlight_Controller_Response_Response $response
    ) {
        $this->getServices();
        $this->log = $this->services->get('logger');
        parent::__construct($request, $response);
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $sort        Contains an array of Ext JS sort conditions
     * @param array $filter      Contains an array of Ext JS filters
     * @param array $wholeParams Contains all passed request parameters
     *
     * @return array
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        $builder = $this->getListQuery();
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $filter = $this->getFilterConditions(
            $filter,
            $this->model,
            $this->alias,
            $this->filterFields
        );

        $sort = $this->getSortConditions(
            $sort,
            $this->model,
            $this->alias,
            $this->sortFields
        );

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        $this->finalizeListQuery($builder);

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();

        return ['success' => true, 'data' => $data, 'total' => $count];
    }

    /**
     * Contains the logic to get the detailed information of a single record.
     * The function expects the model identifier value as parameter.
     * To add additional data to the detailed information you can override the
     * {@link #getAdditionalDetailData} function.
     *
     * To extend the query builder object to select more detailed information,
     * you can override the {@link #getDetailQuery} function.
     *
     * @param int $id - Identifier of the doctrine model
     *
     * @return array
     */
    public function getDetail($id)
    {
        $builder = $this->getDetailQuery($id);
        $this->finalizeDetailQuery($builder);
        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->current();
        if (!$data) {
            $data = [];
        }
        $data = $this->getAdditionalDetailData($data);

        return ['success' => true, 'data' => $data];
    }

    protected function finalizeListQuery(QueryBuilder $builder) {
    }

    protected function finalizeDetailQuery(QueryBuilder $builder) {
    }

}