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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class SimilarProductsGateway implements SimilarProductsGatewayInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $numbers = $this->getList([$product], $context);

        return array_shift($numbers);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->select(['product.id', 'similarVariant.ordernumber as number'])
            ->from('s_articles_similar', 'similar')
            ->innerJoin('similar', 's_articles', 'product', 'product.id = similar.articleID')
            ->innerJoin('similar', 's_articles', 'similarArticles', 'similarArticles.id = similar.relatedArticle')
            ->innerJoin('similarArticles', 's_articles_details', 'similarVariant', 'similarVariant.id = similarArticles.main_detail_id')
            ->where('product.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $related = [];
        foreach ($data as $productId => $row) {
            $related[(int) $productId] = array_column($row, 'number');
        }

        return $related;
    }
}
