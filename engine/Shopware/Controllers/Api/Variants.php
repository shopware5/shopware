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

class Shopware_Controllers_Api_Variants extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\Variant
     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('variant');
    }

    /**
     * Get list of variants
     *
     * GET /api/variants/
     */
    public function indexAction()
    {
        $offset = $this->Request()->getParam('start', 0);
        $limit  = $this->Request()->getParam('limit', 1000);
        $filter = $this->Request()->getParam('filter', []);
        $sort   = $this->Request()->getParam('sort', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort, [
            'considerTaxInput' => (boolean) $this->Request()->getParam('considerTaxInput', false)
        ]);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one variant
     *
     * GET /api/variants/{id}
     */
    public function getAction()
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (boolean) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $articleDetail = $this->resource->getOneByNumber($id, [
                'considerTaxInput' => $this->Request()->getParam('considerTaxInput')
            ]);
        } else {
            $articleDetail = $this->resource->getOne($id, [
                'considerTaxInput' => $this->Request()->getParam('considerTaxInput')
            ]);
        }

        $this->View()->assign('data', $articleDetail);
        $this->View()->assign('success', true);
    }

    /**
     * Create new variant
     *
     * POST /api/variants
     */
    public function postAction()
    {
        $article = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'variants/' . $article->getId();
        $data = [
            'id'       => $article->getId(),
            'location' => $location
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->setHeader('Location', $location);
    }

    /**
     * Update variant
     *
     * PUT /api/variants/{id}
     */
    public function putAction()
    {
        $id = $this->Request()->getParam('id');
        $params = $this->Request()->getPost();
        $useNumberAsId = (boolean) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $article = $this->resource->updateByNumber($id, $params);
        } else {
            $article = $this->resource->update($id, $params);
        }

        $location = $this->apiBaseUrl . 'variants/' . $article->getId();
        $data = [
            'id'       => $article->getId(),
            'location' => $location
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete a given variant
     *
     * DELETE /api/variants/{id}
     */
    public function deleteAction()
    {
        $id = $this->Request()->getParam('id');
        $useNumberAsId = (boolean) $this->Request()->getParam('useNumberAsId', 0);

        if ($useNumberAsId) {
            $this->resource->deleteByNumber($id);
        } else {
            $this->resource->delete($id);
        }

        $this->View()->assign(['success' => true]);
    }
}
