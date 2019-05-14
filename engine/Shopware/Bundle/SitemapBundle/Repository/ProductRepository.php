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

namespace Shopware\Bundle\SitemapBundle\Repository;

use Shopware\Bundle\SearchBundle\Condition\LastProductIdCondition;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var StoreFrontCriteriaFactoryInterface
     */
    private $storeFrontCriteriaFactory;

    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param int $batchSize
     */
    public function __construct(
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        ProductNumberSearchInterface $productNumberSearch,
        $batchSize
    ) {
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->productNumberSearch = $productNumberSearch;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function createCriteria(ShopContextInterface $shopContext, $lastId = null)
    {
        $criteria = $this->storeFrontCriteriaFactory
            ->createBaseCriteria([$shopContext->getShop()->getCategory()->getId()], $shopContext);
        $criteria->setFetchCount(false);
        $criteria->limit($this->batchSize);

        if ($lastId !== null) {
            $criteria->addBaseCondition(new LastProductIdCondition($lastId));
        }

        return $criteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIds(ShopContextInterface $shopContext, $lastId = null)
    {
        $criteria = $this->createCriteria($shopContext, $lastId);

        $productNumberSearchResult = $this->productNumberSearch->search($criteria, $shopContext);

        if (count($productNumberSearchResult->getProducts()) === 0) {
            return [];
        }

        // Load all available product ids
        return array_map(function (BaseProduct $baseProduct) {
            return $baseProduct->getId();
        }, array_values($productNumberSearchResult->getProducts()));
    }
}
