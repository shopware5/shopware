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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ManufacturerProvider implements BenchmarkProviderInterface
{
    private const NAME = 'manufacturers';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopContext = $shopContext;

        return [
            'total' => $this->getTotalSuppliers(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalSuppliers()
    {
        $productIds = $this->getProductIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return (int) $queryBuilder->select('COUNT(DISTINCT product.supplierID)')
            ->from('s_articles', 'product')
            ->where('product.id IN (:productIds)')
            ->setParameter(':productIds', $productIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getProductIds()
    {
        $categoryIds = $this->getPossibleCategoryIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('DISTINCT productCat.articleID')
            ->from('s_articles_categories', 'productCat')
            ->where('productCat.categoryID IN (:categoryIds)')
            ->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return array
     */
    private function getPossibleCategoryIds()
    {
        $categoryId = $this->shopContext->getShop()->getCategory()->getId();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :categoryIdPath')
            ->orWhere('category.id = :categoryId')
            ->setParameter(':categoryId', $categoryId)
            ->setParameter(':categoryIdPath', '%|' . $categoryId . '|%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
