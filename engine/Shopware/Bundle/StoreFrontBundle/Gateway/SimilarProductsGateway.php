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
     * @param Struct\BaseProduct[]        $products
     * @param Struct\ShopContextInterface $context
     *
     * @return array Indexed by the product number
     */
    public function getList($products, Struct\ShopContextInterface $context)
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

    /**
     * Returns an array which contains the order number of
     * each similar products for the provided product.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
     * - Selects products which are in the same category
     *
     * Example result: array('SW101', 'SW102')
     *
     * @param Struct\BaseProduct          $product
     * @param Struct\ShopContextInterface $context
     *
     * @return array Array of order numbers
     *
     * @deprecated since version 5.1.4, to be removed in 5.3 - Use SimilarProductCondition instead
     */
    public function getByCategory(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $products = $this->getListByCategory([$product], $context);

        return array_shift($products);
    }

    /**
     * Returns an array which contains the order number of
     * each similar products for the provided products.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
     * - Selects products which are in the same category
     *
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
     * @param Struct\BaseProduct[]        $products
     * @param Struct\ShopContextInterface $context
     *
     * @return array Indexed by the product number
     *
     * @deprecated since version 5.1.4, to be removed in 5.3 - Use SimilarProductCondition instead
     */
    public function getListByCategory($products, Struct\ShopContextInterface $context)
    {
        if (!$this->config->offsetExists('similarLimit') || $this->config->get('similarLimit') <= 0) {
            return [];
        }

        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $categoryId = 1;
        if ($context->getShop() && $context->getShop()->getCategory()) {
            $categoryId = $context->getShop()->getCategory()->getId();
        }

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'main.articleID',
            "GROUP_CONCAT(subVariant.ordernumber SEPARATOR '|') as similar",
        ]);

        $query->from('s_articles_categories', 'main')
            ->innerJoin('main', 's_articles_categories', 'sub', 'sub.categoryID = main.categoryID AND sub.articleID != main.articleID')
            ->innerJoin('sub', 's_articles_details', 'subVariant', 'subVariant.articleID = sub.articleID AND subVariant.kind = 1')
            ->innerJoin('main', 's_categories', 'category', 'category.id = sub.categoryID AND category.id = main.categoryID')
            ->where('main.articleID IN (:ids)')
            ->andWhere('category.path LIKE :path')
            ->groupBy('main.articleID')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':path', '%|' . (int) $categoryId . '|');

        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $limit = (int) $this->config->get('similarLimit');

        $result = [];
        foreach ($data as $row) {
            $similar = explode('|', $row['similar']);
            $result[$row['articleID']] = array_slice($similar, 0, $limit);
        }

        return $result;
    }
}
