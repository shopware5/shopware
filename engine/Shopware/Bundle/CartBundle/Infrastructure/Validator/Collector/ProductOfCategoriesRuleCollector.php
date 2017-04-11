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

namespace Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Product\CalculatedProduct;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorInterface;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\ProductOfCategoriesRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ProductOfCategoriesRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductOfCategoriesRuleCollector implements RuleDataCollectorInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function collect(
        RuleCollection $rules,
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ) {
        $categoryRules = $rules->filterInstance(ProductOfCategoriesRule::class);

        if ($categoryRules->count() === 0) {
            return;
        }

        $categoryIds = [];
        /** @var ProductOfCategoriesRule $rule */
        foreach ($categoryRules as $rule) {
            $categoryIds = array_merge($categoryIds, $rule->getCategoryIds());
        }

        $numbers = $calculatedCart->getCalculatedLineItems()->filterInstance(CalculatedProduct::class)->getKeys();

        if (empty($numbers)) {
            return;
        }

        $categories = $this->fetchCategories($categoryIds, $numbers);

        $collection->add(
            new ProductOfCategoriesRuleData($categories)
        );
    }

    private function fetchCategories(array $categoryIds, array $numbers)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'category.categoryID',
            'variant.ordernumber',
        ]);

        $query->from('s_articles_categories_ro', 'category');
        $query->innerJoin('category', 's_articles_details', 'variant', 'variant.articleID = category.articleID');
        $query->andWhere('category.categoryID IN (:categoryIds)');
        $query->andWhere('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);
        $query->setParameter(':categoryIds', $categoryIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);
    }
}
