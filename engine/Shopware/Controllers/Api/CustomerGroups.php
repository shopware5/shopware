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

use Shopware\Components\Api\Resource\CustomerGroup;

class Shopware_Controllers_Api_CustomerGroups extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\CustomerGroup
     */
    protected $resource;

    public function __construct(CustomerGroup $customerGroup)
    {
        $this->resource = $customerGroup;
        parent::__construct();
    }

    /**
     * Get list of customergroups
     *
     * GET /api/customerGroups/
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
     * Get one customergroup
     *
     * GET /api/customergroup/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');

        $result = $this->resource->getOne($id);

        $this->View()->assign('data', $result);
        $this->View()->assign('success', true);
    }

    /**
     * Create new customerGroup
     *
     * POST /api/customerGroups
     */
    public function postAction(): void
    {
        $result = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'customerGroups/' . $result->getId();
        $data = [
            'id' => $result->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update customerGroup
     *
     * PUT /api/customerGroups/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();

        $result = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'customerGroups/' . $result->getId();
        $data = [
            'id' => $result->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete customerGroup
     *
     * DELETE /api/customerGroups/{id}
     */
    public function deleteAction(): void
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign(['success' => true]);
    }
}
