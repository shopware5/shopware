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

use Shopware\Components\Api\Resource\Shop;

class Shopware_Controllers_Api_Shops extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\Shop
     */
    protected $resource;

    public function __construct(Shop $shop)
    {
        $this->resource = $shop;
        parent::__construct();
    }

    /**
     * Get list of shops
     *
     * GET /api/shops/
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
     * Get one shop
     *
     * GET /api/shops/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');

        $shop = $this->resource->getOne($id);

        $this->View()->assign('data', $shop);
        $this->View()->assign('success', true);
    }

    /**
     * Create new shop
     *
     * POST /api/shop
     */
    public function postAction(): void
    {
        $shop = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'shops/' . $shop->getId();
        $data = [
            'id' => $shop->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update shop
     *
     * PUT /api/shops/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();

        $shop = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'shops/' . $shop->getId();
        $data = [
            'id' => $shop->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete shop
     *
     * DELETE /api/shop/{id}
     */
    public function deleteAction(): void
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign(['success' => true]);
    }
}
