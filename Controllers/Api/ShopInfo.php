<?php

class Shopware_Controllers_Api_ShopInfo extends Shopware_Controllers_Api_Rest
{
    /**
     * @var \Shopware\Components\Api\Resource\ShopInfo     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('ShopInfo');
    }

    /**
     * Get list of entities
     *
     * GET /api/shopinfo/
     */
    public function indexAction()
    {
        $limit  = $this->Request()->getParam('limit', 1000);
        $offset = $this->Request()->getParam('start', 0);
        $sort   = $this->Request()->getParam('sort', array());
        $filter = $this->Request()->getParam('filter', array());

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one entity
     *
     * GET /api/shopinfo/{id}
     */
    public function getAction()
    {
        $id = $this->Request()->getParam('id');

        $entity = $this->resource->getOne($id);

        $this->View()->assign('data', $entity);
        $this->View()->assign('success', true);
    }

    /**
     * Create new entity
     *
     * POST /api/shopinfo}
     */
    public function postAction()
    {
        $entity = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'shopinfo/' . $entity->getId();
        $data = array(
            'id'       => $entity->getId(),
            'location' => $location
        );

        $this->View()->assign(array('success' => true, 'data' => $data));
        $this->Response()->setHeader('Location', $location);
    }

    /**
     * Update entity
     *
     * PUT /api/shopinfo/{id}
     */
    public function putAction()
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();


        $entity = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'shopinfo/' . $entity->getId();
        $data = array(
            'id'       => $entity->getId(),
            'location' => $location
        );

        $this->View()->assign(array('success' => true, 'data' => $data));
        $this->Response()->setHeader('Location', $location);
    }

    /**
     * Delete entity
     *
     * DELETE /api/shopinfo/{id}
     */
    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign(array('success' => true));
    }
}
