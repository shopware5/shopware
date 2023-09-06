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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use RuntimeException;
use Shopware\Bundle\StoreFrontBundle\Service\ProductNumberServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class ProductNumberService implements ProductNumberServiceInterface
{
    private Connection $connection;

    private Shopware_Components_Config $config;

    public function __construct(
        Connection $connection,
        Shopware_Components_Config $config
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

        $number = $query->execute()->fetch(PDO::FETCH_COLUMN);

        if (!$number) {
            throw new RuntimeException(sprintf('No valid product number found by id %d', $productId));
        }

        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableNumber($number, ShopContextInterface $context, $selection = [])
    {
        $productId = $this->getProductIdByNumber($number);

        if ($productId === 0) {
            throw new RuntimeException(sprintf('No valid product id found for product with number "%s"', $number));
        }

        if (!$this->isProductAvailableInShop($productId, $context->getShop())) {
            throw new RuntimeException(sprintf('Product with number "%s" is not available in current shop', $number));
        }

        $selected = '';
        if (!empty($selection)) {
            $selected = $this->getNumberBySelection($productId, $selection);
        }

        if ($selected !== '') {
            return $selected;
        }

        if ($this->isNumberAvailable($number)) {
            return $number;
        }

        if ($this->hasNotificationsActive($productId)) {
            return $number;
        }

        $selected = $this->findFallbackById($productId);
        if ($selected === '') {
            throw new RuntimeException(sprintf('No active product variant found for product with number "%s" and id "%s"', $number, $productId));
        }

        return $selected;
    }

    private function hasNotificationsActive(int $productId): bool
    {
        $pluginActive = $this->connection->fetchColumn("SELECT `active` FROM s_core_plugins WHERE name = 'Notification'");
        $notificationEnabled = $this->connection->fetchColumn('SELECT `notification` FROM s_articles WHERE id = :productId', [':productId' => $productId]);

        return $pluginActive && $notificationEnabled;
    }

    private function findFallbackById(int $productId): string
    {
        $selected = $this->getMainVariantNumberById($productId);
        if ($selected !== '') {
            return $selected;
        }

        $selected = $this->getAvailableFallbackVariant($productId);
        if ($selected !== '') {
            return $selected;
        }

        return $this->getFallbackVariant($productId);
    }

    /**
     * Returns the product id of the provided order number.
     */
    private function getProductIdByNumber(string $number): int
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('variant.articleID')
            ->from('s_articles_details', 'variant')
            ->where('variant.ordernumber = :number')
            ->setParameter(':number', $number);

        return (int) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns a single order number for the passed product configuration selection.
     *
     * @param array<int, int> $selection
     */
    private function getNumberBySelection(int $productId, array $selection): string
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

        return (string) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    private function isNumberAvailable(string $number): bool
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('variant.ordernumber = :number')
            ->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)')
            ->setParameter(':number', $number);

        $selected = $query->execute()->fetch(PDO::FETCH_COLUMN);

        return (bool) $selected;
    }

    private function getMainVariantNumberById(int $productId): string
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('variant.id = product.main_detail_id')
            ->andWhere('product.id = :productId')
            ->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)')
            ->setParameter(':productId', $productId);

        return (string) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the first active variant number
     */
    private function getFallbackVariant(int $productId): string
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('product.id = :productId');
        $query->setMaxResults(1);
        $query->setParameter(':productId', $productId);

        return (string) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Returns the first active variant number that is available for purchase
     */
    private function getAvailableFallbackVariant(int $productId): string
    {
        $query = $this->getProductNumberQuery();

        $query->andWhere('product.id = :productId');
        $query->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)');
        $query->setMaxResults(1);
        $query->setParameter(':productId', $productId);

        return (string) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    private function getProductNumberQuery(): QueryBuilder
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
     */
    private function isProductAvailableInShop(int $productId, Shop $shop): bool
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('categories.categoryID')
            ->from('s_articles_categories_ro', 'categories')
            ->where('categories.articleID = :productId')
            ->andWhere('categories.categoryID = :categoryId')
            ->setParameter(':productId', $productId)
            ->setParameter(':categoryId', $shop->getCategory()->getId())
            ->setMaxResults(1);

        return (bool) $query->execute()->fetch(PDO::FETCH_COLUMN);
    }
}
