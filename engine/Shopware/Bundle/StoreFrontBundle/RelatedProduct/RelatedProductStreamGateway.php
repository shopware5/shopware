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

namespace Shopware\Bundle\StoreFrontBundle\RelatedProduct;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Bundle\StoreFrontBundle\ProductStream\ProductStreamHydrator;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class RelatedProductStreamGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var ProductStreamHydrator
     */
    private $hydrator;

    /**
     * @param Connection            $connection
     * @param FieldHelper           $fieldHelper
     * @param ProductStreamHydrator $hydrator
     */
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
     * Returns an array which contains the product stream details of
     * each related product stream for the provided products.
     *
     * Example:
     * Provided products:  SW100, SW200
     *
     * Result:
     * array(
     *    'SW100' => array({Struct\ProductStream}, {Struct\ProductStream})
     *    'SW200' => array({Struct\ProductStream}, {Struct\ProductStream})
     * )
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Product\BaseProduct[] $products
     * @param TranslationContext                                      $context
     *
     * @return array indexed by the product number
     */
    public function getList($products, TranslationContext $context)
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

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

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
