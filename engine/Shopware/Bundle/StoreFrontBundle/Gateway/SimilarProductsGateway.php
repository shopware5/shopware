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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SimilarProductsGateway
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param Connection                  $connection
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        Connection $connection,
        \Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * Returns an array which contains the order number of
     * each similar products for the provided products.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
     *
     * Example:
     * Provided products:  SW100, SW200
     *
     * Result:
     * array(
     *    'SW100' => array('SW101', 'SW102')
     *    'SW200' => array('SW201', 'SW202')
     * )
     *
     * @param Struct\BaseProduct[]      $products
     * @param Struct\TranslationContext $context
     *
     * @return array Indexed by the product number
     */
    public function getList($products, Struct\TranslationContext $context)
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

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $related = [];
        foreach ($data as $productId => $row) {
            $related[$productId] = array_column($row, 'number');
        }

        return $related;
    }
}
