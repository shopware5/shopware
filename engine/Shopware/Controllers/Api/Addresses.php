<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Components\Api\Resource\Address;

class Shopware_Controllers_Api_Addresses extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Address
     */
    protected $resource;

    public function __construct(Address $resource)
    {
        parent::__construct();
        $this->resource = $resource;
    }

    /**
     * Get list of addresses
     *
     * GET /api/addresses/
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
     * Get one address
     *
     * GET /api/addresses/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');

        $address = $this->resource->getOne($id);

        $this->View()->assign('data', $address);
        $this->View()->assign('success', true);
    }

    /**
     * Create new address
     *
     * POST /api/addresses
     */
    public function postAction(): void
    {
        $address = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'addresses/' . $address->getId();
        $data = [
            'id' => $address->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update address
     *
     * PUT /api/addresses/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();

        $address = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'addresses/' . $address->getId();
        $data = [
            'id' => $address->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete address
     *
     * DELETE /api/addresses/{id}
     */
    public function deleteAction(): void
    {
        $id = $this->Request()->getParam('id');
        $this->resource->delete($id);

        $this->View()->assign(['success' => true]);
    }
}
