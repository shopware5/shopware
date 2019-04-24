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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Service\ProductNumberServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductNumberService implements ProductNumberServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        Connection $connection,
        \Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getMainProductNumberById($productId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('variant.ordernumber')
            ->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_articles', 'product', 'product.main_detail_id = variant.id')
            ->where('variant.articleID = :id')
            ->setParameter(':id', $productId);

        $number = $query->execute()->fetch(\PDO::FETCH_COLUMN);

        if (!$number) {
            throw new \RuntimeException(sprintf('No valid product number found by id %d', $productId));
        }

        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableNumber($number, ShopContextInterface $context, $selection = [])
    {
        $productId = $this->getProductIdByNumber($number);
        if (!$productId) {
            throw new \RuntimeException(sprintf('No valid product id found for product with number "%s"', $number));
        }

        if (!$this->isProductAvailableInShop($productId, $context->getShop())) {
            throw new \RuntimeException(sprintf('Product with number "%s" is not available in current shop', $number));
        }

        $selected = null;
        if (!empty($selection)) {
            $selected = $this->getNumberBySelection($productId, $selection);
        }

        if ($selected) {
            return $selected;
        }

        if ($this->isNumberAvailable($number)) {
            return $number;
        }

        $selected = $this->findFallbackById($productId);
        if (!$selected) {
            throw new \RuntimeException(sprintf('No active product variant found for product with number "%s" and id "%s"', $number, $productId));
        }

        return $selected;
    }

    /**
     * @param int $productId
     *
     * @return string|false
     */
    private function findFallbackById($productId)
    {
        $selected = $this->getMainVariantNumberById($productId);
        if ($selected) {
            return $selected;
        }

        $selected = $this->getAvailableFallbackVariant($productId);
        if ($selected) {
            return $selected;
        }

        return $this->getFallbackVariant($productId);
    }

    /**
     * Returns the product id of the provided order number.
     *
     * @param string $number
     *
     * @return int|null
     */
    private function getProductIdByNumber($number)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('variant.articleID')
            ->from('s_articles_details', 'variant')
            ->where('variant.ordernumber = :number')
            ->setParameter(':number', $number);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns a single order number for the passed product configuration selection.
     *
     * @param int $productId
     *
     * @return string|false
     */
    private function getNumberBySelection($productId, array $selection)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['variant.ordernumber'])
            ->from('s_articles_details', 'variant')
            ->where('variant.articleID = :productId')
            ->andWhere('variant.active = 1')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->setParameter(':productId', $productId);

        foreach ($selection as $optionId) {
            $alias = 'option_' . (int) $optionId;

            $query->innerJoin(
                'variant',
                's_article_configurator_option_relations',
                $alias,
                'variant.id = ' . $alias . '.article_id
                 AND ' . $alias . '.option_id = :' . $alias
            );
            $query->setParameter(':' . $alias, (int) $optionId);
        }

        if ($this->config->get('hideNoInstock')) {
            $query->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID');
            $query->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)');
        }

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @param string $number
     *
     * @return bool
     */
    private function isNumberAvailable($number)
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('variant.ordernumber = :number')
            ->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)')
            ->setParameter(':number', $number);

        $statement = $query->execute();
        $selected = $statement->fetch(\PDO::FETCH_COLUMN);

        return (bool) $selected;
    }

    /**
     * @param int $productId
     *
     * @return string|false
     */
    private function getMainVariantNumberById($productId)
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('variant.id = product.main_detail_id')
            ->andWhere('product.id = :productId')
            ->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)')
            ->setParameter(':productId', $productId);

        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns the first active variant number
     *
     * @param int $productId
     *
     * @return string|false
     */
    private function getFallbackVariant($productId)
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('product.id = :productId');
        $query->setMaxResults(1);
        $query->setParameter(':productId', $productId);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns the first active variant number that is available for purchase
     *
     * @param int $productId
     *
     * @return string|false
     */
    private function getAvailableFallbackVariant($productId)
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('product.id = :productId');
        $query->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)');
        $query->setMaxResults(1);
        $query->setParameter(':productId', $productId);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getProductNumberQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['variant.ordernumber']);
        $query->from('s_articles_details', 'variant');
        $query->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID AND variant.active = 1');
        $query->setMaxResults(1);

        return $query;
    }

    /**
     * Validates if the product is available in the current shop
     *
     * @param int  $productId
     * @param Shop $shop
     *
     * @return string|null
     */
    private function isProductAvailableInShop($productId, $shop)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('categories.categoryID')
            ->from('s_articles_categories_ro', 'categories')
            ->where('categories.articleID = :productId')
            ->andWhere('categories.categoryID = :categoryId')
            ->setParameter(':productId', $productId)
            ->setParameter(':categoryId', $shop->getCategory()->getId())
            ->setMaxResults(1);

        return $query->execute()->fetch(\PDO::FETCH_COLUMN);
    }
}
