<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\RelatedProductsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;

class RelatedProductsGateway implements RelatedProductsGatewayInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product)
    {
        $numbers = $this->getList([$product]);

        return array_shift($numbers);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->select(['product.id', 'relatedVariant.ordernumber as number']);

        $query->from('s_articles_relationships', 'relation')
            ->innerJoin('relation', 's_articles', 'product', 'product.id = relation.articleID')
            ->innerJoin('relation', 's_articles', 'relatedArticles', 'relatedArticles.id = relation.relatedArticle')
            ->innerJoin('relatedArticles', 's_articles_details', 'relatedVariant', 'relatedVariant.id = relatedArticles.main_detail_id')
            ->where('product.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $related = [];
        foreach ($data as $productId => $row) {
            $related[$productId] = array_column($row, 'number');
        }

        return $related;
    }
}
