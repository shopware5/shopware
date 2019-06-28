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

use Shopware\Components\Api\Resource\CustomerStream;

class Shopware_Controllers_Api_CustomerStreams extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\CustomerStream
     */
    protected $resource;

    public function __construct(CustomerStream $resource)
    {
        $this->resource = $resource;
        parent::__construct();
    }

    /**
     * GET /api/customer_streams/
     */
    public function indexAction(): void
    {
        $limit = (int) $this->Request()->getParam('limit', 100);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Returns the customers of a stream or of a collection of conditions
     * GET /api/customer_streams/{id}
     */
    public function getAction(): void
    {
        $customers = $this->resource->getOne(
            $this->Request()->getParam('id'),
            (int) $this->Request()->getParam('offset', 0),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('conditions'),
            $this->Request()->getParam('sortings')
        );

        $this->View()->assign('data', $customers->getCustomers());
        $this->View()->assign('total', $customers->getTotal());
        $this->View()->assign('success', true);
    }

    /**
     * POST /api/customer_streams
     * POST /api/customer_streams?buildSearchIndex=1
     */
    public function postAction(): void
    {
        if ($this->Request()->has('buildSearchIndex')) {
            $this->resource->buildSearchIndex(0, true);
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
        $this->Response()->headers->set('location', $location);
    }

    /**
     * PUT /api/customer_streams/{id}
     */
    public function putAction(): void
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
    public function deleteAction(): void
    {
        $this->resource->delete(
            $this->Request()->getParam('id')
        );

        $this->View()->assign(['success' => true]);
    }
}
