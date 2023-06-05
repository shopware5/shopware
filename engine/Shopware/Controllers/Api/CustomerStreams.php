<?php

declare(strict_types=1);
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
     * @var CustomerStream
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
        $request = $this->Request();

        $limit = (int) $request->getParam('limit', 100);
        $offset = (int) $request->getParam('start', 0);
        $sort = $request->getParam('sort', []);
        $filter = $request->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
    }

    /**
     * Returns the customers of a stream or of a collection of conditions
     * GET /api/customer_streams/{id}
     */
    public function getAction(): void
    {
        $request = $this->Request();

        $streamId = $request->getParam('id');
        if ($streamId !== null) {
            $streamId = (int) $streamId;
        }
        $customerNumberSearchResult = $this->resource->getOne(
            $streamId,
            (int) $request->getParam('offset', 0),
            (int) $request->getParam('limit', 50),
            $request->getParam('conditions', ''),
            $request->getParam('sortings', '')
        );

        $this->View()->assign('data', $customerNumberSearchResult->getCustomers());
        $this->View()->assign('total', $customerNumberSearchResult->getTotal());
        $this->View()->assign('success', true);
    }

    /**
     * POST /api/customer_streams
     * POST /api/customer_streams?buildSearchIndex=1
     */
    public function postAction(): void
    {
        $request = $this->Request();

        if ($request->has('buildSearchIndex')) {
            $this->resource->buildSearchIndex(0, true);
            $this->resource->cleanupIndexSearchIndex();
            $this->View()->assign(['success' => true]);

            return;
        }

        $stream = $this->resource->create(
            $request->getPost(),
            $request->getParam('indexStream')
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
        $request = $this->Request();

        $customerStream = $this->resource->update(
            (int) $request->getParam('id'),
            $request->getPost(),
            $request->getParam('indexStream')
        );

        $location = $this->apiBaseUrl . 'customer_streams/' . $customerStream->getId();
        $data = [
            'id' => $customerStream->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * DELETE /api/customer_streams/{id}
     */
    public function deleteAction(): void
    {
        $streamId = (int) $this->Request()->getParam('id');
        $this->resource->delete($streamId);

        $this->View()->assign(['success' => true]);
    }
}
