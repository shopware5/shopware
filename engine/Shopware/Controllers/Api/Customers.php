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

use Shopware\Components\Api\Resource\Customer;

class Shopware_Controllers_Api_Customers extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Customer
     */
    protected $resource;

    public function __construct(Customer $customer)
    {
        $this->resource = $customer;
        parent::__construct();
    }

    /**
     * Get list of customers
     *
     * GET /api/customers/
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
     * Get one customer
     *
     * GET /api/customers/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $customer = $this->resource->getOneByNumber($id);
        } else {
            $customer = $this->resource->getOne($id);
        }

        $this->View()->assign('data', $customer);
        $this->View()->assign('success', true);
    }

    /**
     * Create new customer
     *
     * POST /api/customers
     */
    public function postAction(): void
    {
        $customer = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'customers/' . $customer->getId();
        $data = [
            'id' => $customer->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update customer
     *
     * PUT /api/customers/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);
        $params = $this->Request()->getPost();

        if ($useNumberAsId) {
            $customer = $this->resource->updateByNumber($id, $params);
        } else {
            $customer = $this->resource->update($id, $params);
        }

        $location = $this->apiBaseUrl . 'customers/' . $customer->getId();
        $data = [
            'id' => $customer->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete customer
     *
     * DELETE /api/customers/{id}
     */
    public function deleteAction(): void
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $this->resource->deleteByNumber($id);
        } else {
            $this->resource->delete($id);
        }

        $this->View()->assign(['success' => true]);
    }
}
