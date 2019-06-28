<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use Shopware\Components\Api\Resource\Order;

class Shopware_Controllers_Api_Orders extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\Order
     */
    protected $resource;

    public function __construct(Order $order)
    {
        $this->resource = $order;
        parent::__construct();
    }

    /**
     * Get list of orders
     *
     * GET /api/orders/
     */
    public function indexAction(): void
    {
        $limit = (int) $this->Request()->getParam('limit', 1000);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one order
     *
     * GET /api/orders/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $order = $this->resource->getOneByNumber($id);
        } else {
            $order = $this->resource->getOne($id);
        }

        $this->View()->assign('data', $order);
        $this->View()->assign('success', true);
    }

    /**
     * Create new order
     *
     * POST /api/orders
     */
    public function postAction(): void
    {
        $order = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'orders/' . $order->getId();
        $data = [
            'id' => $order->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update order
     *
     * PUT /api/orders/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);
        $params = $this->Request()->getPost();

        if ($useNumberAsId) {
            $order = $this->resource->updateByNumber($id, $params);
        } else {
            $order = $this->resource->update($id, $params);
        }

        $location = $this->apiBaseUrl . 'orders/' . $order->getId();
        $data = [
            'id' => $order->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }
}
