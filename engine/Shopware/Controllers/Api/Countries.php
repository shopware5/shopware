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

use Shopware\Components\Api\Resource\Country;

class Shopware_Controllers_Api_Countries extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Country
     */
    protected $resource;

    public function __construct(Country $country)
    {
        $this->resource = $country;
        parent::__construct();
    }

    /**
     * Get list of countries
     *
     * GET /api/countries
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
     * Get one country
     *
     * GET /api/countries/{id}
     */
    public function getAction(): void
    {
        $id = $this->Request()->getParam('id');

        $country = $this->resource->getOne($id);

        $this->View()->assign('data', $country);
        $this->View()->assign('success', true);
    }

    /**
     * Create country
     *
     * POST /api/countries
     */
    public function postAction(): void
    {
        $params = $this->Request()->getPost();

        $country = $this->resource->create($params);

        $location = $this->apiBaseUrl . 'countries/' . $country->getId();
        $data = [
            'id' => $country->getId(),
            'location' => $location,
        ];

        $this->View()->assign('data', $data);
        $this->View()->assign('success', true);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update country
     *
     * PUT /api/countries/{id}
     */
    public function putAction(): void
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();

        $country = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'countries/' . $country->getId();
        $data = [
            'id' => $country->getId(),
            'location' => $location,
        ];

        $this->View()->assign('data', $data);
        $this->View()->assign('success', true);
    }

    /**
     * Delete country
     *
     * DELETE /api/countries/{id}
     */
    public function deleteAction(): void
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign('success', true);
    }
}
