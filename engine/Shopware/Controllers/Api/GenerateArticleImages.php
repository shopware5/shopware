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
use Shopware\Components\Api\Exception as ApiException;

class Shopware_Controllers_Api_GenerateArticleImages extends Shopware_Controllers_Api_Rest
{
    /**
     * @var Shopware\Components\Api\Resource\Article
     */
    protected $resource = null;

    public function init()
    {
        $this->resource = \Shopware\Components\Api\Manager::getResource('article');
    }

    /**
     * Update article
     *
     * PUT /api/generateArticleImages/{id}
     */
    public function putAction()
    {
        $id = $this->Request()->getParam('id');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $useNumberAsId = (bool) $this->Request()->getParam('useNumberAsId', 0);
        $id = $useNumberAsId ? $this->resource->getIdFromNumber($id) : (int) $id;

        if (!$useNumberAsId && $id <= 0) {
            throw new ApiException\CustomValidationException('Invalid article id');
        }

        /** @var \Shopware\Models\Article\Article $article */
        $article = $this->resource->getRepository()->find($id);

        if (!$article) {
            throw new ApiException\NotFoundException(sprintf('Article by id %d not found', $id));
        }

        $this->resource->generateImages($article, (bool) $this->Request()->getParam('force', 0));

        $this->View()->assign(['success' => true]);
    }

    /**
     * Controller Action for the batchAction
     * Blocks batch actions implemented by the extended class
     *
     * @throws RuntimeException
     */
    public function batchAction()
    {
        throw new \Shopware\Components\Api\Exception\BatchInterfaceNotImplementedException('Batch operations not implemented by this resource');
    }

    /**
     * Controller Action for the batchDelete
     * Blocks batch actions implemented by the extended class
     *
     * @throws RuntimeException
     */
    public function batchDeleteAction()
    {
        throw new \Shopware\Components\Api\Exception\BatchInterfaceNotImplementedException('Batch operations not implemented by this resource');
    }
}
