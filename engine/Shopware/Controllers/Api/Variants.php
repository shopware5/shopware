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

use Shopware\Components\Api\Resource\Variant;

class Shopware_Controllers_Api_Variants extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Variant
     */
    protected $resource;

    public function __construct(Variant $resource)
    {
        $this->resource = $resource;
        parent::__construct();
    }

    /**
     * Get list of variants
     *
     * GET /api/variants/
     */
    public function indexAction(): void
    {
        $request = $this->Request();
        $limit = (int) $request->getParam('limit', 1000);
        $offset = (int) $request->getParam('start', 0);
        $filter = $request->getParam('filter', []);
        $sort = $request->getParam('sort', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort, [
            'considerTaxInput' => (bool) $request->getParam('considerTaxInput', false),
        ]);

        $view = $this->View();
        $view->assign($result);
        $view->assign('success', true);
    }

    /**
     * Get one variant
     *
     * GET /api/variants/{id}
     */
    public function getAction(): void
    {
        $request = $this->Request();
        $id = $request->getParam('id');
        $useNumberAsId = (bool) $request->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $variant = $this->resource->getOneByNumber($id, [
                'considerTaxInput' => $request->getParam('considerTaxInput'),
            ]);
        } else {
            $variant = $this->resource->getOne($id, [
                'considerTaxInput' => $request->getParam('considerTaxInput'),
            ]);
        }

        $view = $this->View();
        $view->assign('data', $variant);
        $view->assign('success', true);
    }

    /**
     * Create new variant
     *
     * POST /api/variants
     */
    public function postAction(): void
    {
        $variant = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'variants/' . $variant->getId();
        $data = [
            'id' => $variant->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update variant
     *
     * PUT /api/variants/{id}
     */
    public function putAction(): void
    {
        $request = $this->Request();
        $id = $request->getParam('id');
        $params = $request->getPost();
        $useNumberAsId = (bool) $request->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $variant = $this->resource->updateByNumber($id, $params);
        } else {
            $variant = $this->resource->update($id, $params);
        }

        $location = $this->apiBaseUrl . 'variants/' . $variant->getId();
        $data = [
            'id' => $variant->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete a given variant
     *
     * DELETE /api/variants/{id}
     */
    public function deleteAction(): void
    {
        $request = $this->Request();
        $id = $request->getParam('id');
        $useNumberAsId = (bool) $request->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $this->resource->deleteByNumber($id);
        } else {
            $this->resource->delete($id);
        }

        $this->View()->assign(['success' => true]);
    }
}
