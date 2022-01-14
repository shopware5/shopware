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
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ProductStreamHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\RelatedProductStreamsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class RelatedProductStreamsGateway implements RelatedProductStreamsGatewayInterface
{
    private Connection $connection;

    private FieldHelper $fieldHelper;

    private ProductStreamHydrator $hydrator;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ProductStreamHydrator $hydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
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

        $query->select(['relation.article_id'])
            ->addSelect($this->fieldHelper->getRelatedProductStreamFields());

        $query->from('s_product_streams_articles', 'relation')
            ->innerJoin('relation', 's_product_streams', 'stream', 'stream.id = relation.stream_id')
            ->leftJoin('stream', 's_product_streams_attributes', 'productStreamAttribute', 'productStreamAttribute.streamID = stream.id')
            ->where('relation.article_id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addProductStreamTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_GROUP);

        $related = [];
        foreach ($data as $productId => $productData) {
            $related[$productId] = [];
            foreach ($productData as $row) {
                $related[$productId][] = $this->hydrator->hydrate($row);
            }
        }

        return $related;
    }
}
