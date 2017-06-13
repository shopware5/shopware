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

class Shopware_Controllers_Api_CustomerStreams extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\CustomerStream
     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('customer_stream');
    }

    /**
     * GET /api/customer_streams/
     */
    public function indexAction()
    {
        $limit = $this->Request()->getParam('limit', 100);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Returns the customers of a stream or collection of conditions
     * GET /api/customer_streams/{id}
     */
    public function getAction()
    {
        $customers = $this->resource->getOne(
            $this->Request()->getParam('id'),
            $this->Request()->getParam('offset', 0),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('conditions', null),
            $this->Request()->getParam('sortings', null)
        );

        $this->View()->assign('data', $customers->getCustomers());
        $this->View()->assign('total', $customers->getTotal());
        $this->View()->assign('success', true);
    }

    /**
     * POST /api/customer_streams
     * POST /api/customer_streams?buildSearchIndex=1
     */
    public function postAction()
    {
        if ($this->Request()->has('buildSearchIndex')) {
            $this->resource->buildSearchIndex(null, true);
            $this->resource->cleanupIndexSearchIndex();
            $this->View()->assign(['success' => true]);

            return;
        }

        $stream = $this->resource->create(
            $this->Request()->getPost(),
            $this->Request()->getParam('indexStream')
        );

        $location = $this->apiBaseUrl . 'customer_streams/' . $stream->getId();
        $data = [
            'id' => $stream->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->setHeader('Location', $location);
    }

    /**
     * PUT /api/customer_streams/{id}
     */
    public function putAction()
    {
        $customer = $this->resource->update(
            $this->Request()->getParam('id'),
            $this->Request()->getPost(),
            $this->Request()->getParam('indexStream')
        );

        $location = $this->apiBaseUrl . 'customer_streams/' . $customer->getId();
        $data = [
            'id' => $customer->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * DELETE /api/customer_streams/{id}
     */
    public function deleteAction()
    {
        $this->resource->delete(
            $this->Request()->getParam('id')
        );

        $this->View()->assign(['success' => true]);
    }
}
