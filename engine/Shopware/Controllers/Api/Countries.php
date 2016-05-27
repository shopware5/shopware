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

class Shopware_Controllers_Api_Countries extends Shopware_Controllers_Api_Rest
{
    /**
     * @var \Shopware\Components\Api\Resource\Country
     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('country');
    }

    /**
     * Get list of countries
     *
     * GET /api/countries
     */
    public function indexAction()
    {
        $limit  = $this->Request()->getParam('limit', 1000);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign('data', $result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one country
     *
     * GET /api/countries/{id}
     */
    public function getAction()
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
    public function postAction()
    {
        $params = $this->Request()->getPost();

        $country = $this->resource->create($params);

        $location = $this->apiBaseUrl . 'countries/' . $country->getId();
        $data = [
            'id' => $country->getId(),
            'location' => $location
        ];

        $this->View()->assign('data', $data);
        $this->View()->assign('success', true);
    }

    /**
     * Update country
     *
     * PUT /api/countries/{id}
     */
    public function putAction()
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();

        $country = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'countries/' . $country->getId();
        $data = [
            'id' => $country->getId(),
            'location' => $location
        ];

        $this->View()->assign('data', $data);
        $this->View()->assign('success', true);
    }

    /**
     * Delete country
     *
     * DELETE /api/countries/{id}
     */
    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');

        $this->resource->delete($id);

        $this->View()->assign('success', true);
    }
}
