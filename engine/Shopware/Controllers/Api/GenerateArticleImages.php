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
use Shopware\Components\Api\Resource\Article as ArticleResource;
use Shopware\Models\Article\Article;

class Shopware_Controllers_Api_GenerateArticleImages extends Shopware_Controllers_Api_Rest
{
    /**
     * @var ArticleResource
     */
    protected $resource;

    public function __construct(ArticleResource $resource)
    {
        $this->resource = $resource;
        parent::__construct();
    }

    /**
     * Generate product images
     *
     * PUT /api/generateArticleImages/{id}
     */
    public function putAction(): void
    {
        $request = $this->Request();
        $id = $request->getParam('id');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $useNumberAsId = (bool) $request->getParam('useNumberAsId', 0);
        $id = $useNumberAsId ? $this->resource->getIdFromNumber($id) : (int) $id;

        if (!$useNumberAsId && $id <= 0) {
            throw new ApiException\CustomValidationException('Invalid product id');
        }

        /** @var Article|null $product */
        $product = $this->resource->getRepository()->find($id);

        if (!$product) {
            throw new ApiException\NotFoundException(sprintf('Product by id %d not found', $id));
        }

        $this->resource->generateImages($product, (bool) $request->getParam('force', 0));

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
        throw new ApiException\BatchInterfaceNotImplementedException('Batch operations not implemented by this resource');
    }

    /**
     * Controller Action for the batchDelete
     * Blocks batch actions implemented by the extended class
     *
     * @throws RuntimeException
     */
    public function batchDeleteAction()
    {
        throw new ApiException\BatchInterfaceNotImplementedException('Batch operations not implemented by this resource');
    }
}
