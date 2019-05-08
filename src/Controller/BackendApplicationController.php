<?php

namespace Mxc\Shopware\Plugin\Controller;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Mxc\Shopware\Plugin\Service\ServicesTrait;
use Shopware_Controllers_Backend_Application;
use Throwable;

class BackendApplicationController extends Shopware_Controllers_Backend_Application
{
    use ServicesTrait;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    protected function getLog()
    {
        if (! $this->log) {
            $this->log = $this->getServices()->get('logger');
        }
        return $this->log;
    }

    public function indexAction() {
        try {
            parent::indexAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function listAction()
    {
        try {
            parent::listAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function detailAction()
    {
        try {
            parent::detailAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function createAction()
    {
        try {
            parent::createAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function updateAction()
    {
        try {
            parent::updateAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function deleteAction()
    {
        try {
            parent::deleteAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function searchAssociationAction()
    {
        try {
            parent::searchAssociationAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function reloadAssociationAction()
    {
        try {
            parent::reloadAssociationAction();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    protected function handleException(Throwable $e, bool $rethrow = false) {
        $this->getLog()->except($e, true, $rethrow);
        $this->view->assign([ 'success' => false, 'message' => $e->getMessage() ]);
    }

}