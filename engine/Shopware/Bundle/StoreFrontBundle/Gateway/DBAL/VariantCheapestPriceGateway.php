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
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class VariantCheapestPriceGateway implements Gateway\VariantCheapestPriceGatewayInterface
{
    /**
     * @var Hydrator\PriceHydrator
     */
    private $priceHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var VariantHelperInterface
     */
    private $variantHelper;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\PriceHydrator $priceHydrator,
        \Shopware_Components_Config $config,
        VariantHelperInterface $variantHelper
    ) {
        $this->connection = $connection;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
        $this->variantHelper = $variantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(
        Struct\BaseProduct $product,
        Struct\ShopContextInterface $context,
        Struct\Customer\Group $customerGroup,
        Criteria $criteria
    ) {
        $prices = $this->getList([$product], $context, $customerGroup, $criteria);

        return array_shift($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        $products,
        Struct\ShopContextInterface $context,
        Struct\Customer\Group $customerGroup,
        Criteria $criteria
    ) {
        $variantIds = array_map(function (Struct\BaseProduct $product) {
            return $product->getVariantId();
        }, $products);

        /*
         * Query to select the data of the cheapest price
         */
        $mainQuery = $this->connection->createQueryBuilder();

        /*
         * Contains the cheapest price logic which product price should be selected.
         */
        $cheapestPriceQuery = $this->getCheapestPriceQuery($mainQuery, $criteria);

        $mainQuery->select($this->fieldHelper->getPriceFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect('variantCheapestPrice.ordernumber as __variant_ordernumber')
            ->addSelect('variantCheapestPrice.different_price_count as __different_price_count');

        $mainQuery->from('s_articles_prices', 'price')
            ->innerJoin('price', 's_articles_details', 'variant', 'variant.id = price.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id')
            ->innerJoin('price', '(' . $cheapestPriceQuery->getSQL() . ')', 'variantCheapestPrice', 'variantCheapestPrice.id = price.id');

        $this->fieldHelper->addUnitTranslation($mainQuery, $context);
        $this->fieldHelper->addProductTranslation($mainQuery, $context);
        $this->fieldHelper->addVariantTranslation($mainQuery, $context);
        $this->fieldHelper->addPriceTranslation($mainQuery, $context);

        $mainQuery->setParameter(':customerGroup', $context->getCurrentCustomerGroup()->getKey())
            ->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey())
            ->setParameter(':variants', $variantIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':priceGroupCustomerGroup', $customerGroup->getId());

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $mainQuery->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = [];
        foreach ($data as $row) {
            $product = $row['__variant_ordernumber'];
            $prices[$product]['price'] = $this->priceHydrator->hydrateCheapestPrice($row);
            $prices[$product]['price']->setCustomerGroup($customerGroup);

            $prices[$product]['different_price_count'] = $row['__different_price_count'];
        }

        return $prices;
    }

    public function joinVariantCondition(QueryBuilder $mainQuery, QueryBuilder $cheapestPriceIdQuery, QueryBuilder $cheapestPriceQuery, VariantCondition $condition)
    {
        /** @var VariantCondition $condition */
        $tableKey = $condition->getName();

        $suffix = md5(json_encode($condition));

        $where = [];

        $tableKeys = [];
        $joinCondition = '';
        /** @var VariantCondition $condition */
        foreach ($condition->getOptionIds() as $valueId) {
            $valueKey = ':' . $tableKey . '_' . $valueId . '_' . $suffix;
            $where[] = $tableKey . '.option_id = ' . $valueKey;

            /*
             * Set the parameter of the options to the main query
             */
            $mainQuery->setParameter($valueKey, $valueId);

            if (array_key_exists($tableKey, $tableKeys)) {
                continue;
            }

            $cheapestPriceQuery->addSelect($tableKey . '.option_id as ' . $tableKey);
            $tableKeys[$tableKey] = $tableKey;
            $joinCondition .= ' ' . $tableKey . '.option_id = ' . $tableKey . ' AND ';
        }

        $where = implode(' OR ', $where);

        /*
         * Join the variant options to the query for the cheapest price
         */
        $cheapestPriceQuery->innerJoin(
            'prices',
            's_article_configurator_option_relations',
            $tableKey,
            'variant.id = ' . $tableKey . '.article_id
             AND (' . $where . ')'
        );

        /*
         * Join the variant options to the query for the id of the cheapest price
         */
        $cheapestPriceIdQuery->innerJoin(
            'details',
            's_article_configurator_option_relations',
            $tableKey,
            'details.id = ' . $tableKey . '.article_id
             AND (' . $where . ')'
        );

        return $joinCondition;
    }

    /**
     * Pre selection of the cheapest prices.
     *
     * @return QueryBuilder
     */
    private function getCheapestPriceQuery(QueryBuilder $mainQuery, Criteria $criteria)
    {
        /*
         * Query to get the cheapest price
         */
        $cheapestPriceQuery = $this->connection->createQueryBuilder();

        $cheapestPriceQuery->select(
            'IF (prices.id IS NULL, defaultPrices.id, prices.id) as id,
             IF (prices.articleID IS NULL, defaultPrices.articleID, prices.articleID) as articleID,
             IF (prices.price IS NULL, defaultPrices.price, prices.price) as price,
            
            variant.minpurchase'
        )
            ->from('s_articles_details', 'variant');

        $cheapestPriceQuery->leftJoin(
            'variant',
            's_articles_prices',
            'prices',
            'variant.id = prices.articledetailsID AND prices.pricegroup = :customerGroup'
        );

        $cheapestPriceQuery->leftJoin(
            'variant',
            's_articles_prices',
            'defaultPrices',
            'variant.id = defaultPrices.articledetailsID AND defaultPrices.pricegroup = :fallbackCustomerGroup'
        );

        /*
         * Joins the products for the closeout validation.
         * Required to select only product prices which product variant can be added to the basket and purchased
         */
        $cheapestPriceQuery->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID'
        );

        $cheapestPriceQuery->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = 1
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );

        $cheapestPriceQuery->where('variant.active = 1');

        /*
         * This part of the query handles the closeout products.
         *
         * The `laststock` column contains "1" if the product is a closeout product.
         * In the case that the product contains the closeout flag,
         * the stock and minpurchase are used as they defined in the database
         *
         * In the case that the product isn't a closeout product,
         * the stock and minpurchase are set to 0
         */
        $cheapestPriceQuery->andWhere(
            '(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)'
        );

        /*
         * Query to get the id of the cheapest price
         */
        $cheapestPriceIdQuery = $this->connection->createQueryBuilder();

        $joinCondition = ' cheapestPrices.articleID = details.articleID ';
        /** @var VariantCondition $condition */
        foreach ($criteria->getConditionsByClass(VariantCondition::class) as $condition) {
            if ($condition->expandVariants()) {
                $joinCondition = $this->joinVariantCondition($mainQuery, $cheapestPriceIdQuery, $cheapestPriceQuery, $condition) . $joinCondition;
            }
        }

        /*
         * Last graduation configuration only needs to use for the cheapest price, not for the different price count.
         * Get the cheapest price of the fallback customer group, if no price of the current customer group is available.
         */
        $countSubQuery = clone $cheapestPriceQuery;
        $graduation = 'IF(prices.id IS NOT NULL, prices.from = 1, defaultPrices.from = 1)';
        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $graduation = "CASE WHEN prices.id IS NOT NULL THEN
                                (IF(priceGroup.id IS NOT NULL, prices.from = 1, prices.to = 'beliebig'))
                           ELSE
                                (IF(priceGroup.id IS NOT NULL, defaultPrices.from = 1, defaultPrices.to = 'beliebig'))
                           END";
        }
        $cheapestPriceQuery->andWhere($graduation);

        $countQuery = clone $cheapestPriceIdQuery;
        $cheapestPriceIdQuery->select('cheapestPrices.id');
        $cheapestPriceIdQuery->from('s_articles_details', 'details');
        $cheapestPriceIdQuery->innerJoin('details', '(' . $cheapestPriceQuery->getSQL() . ')', 'cheapestPrices', $joinCondition);
        $cheapestPriceIdQuery->where('details.id = mainDetail.id');

        if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
            /*
             * Sorting by the cheapest available price
             */
            $cheapestPriceIdQuery->orderBy('(cheapestPrices.price * cheapestPrices.minpurchase)');
        } else {
            /*
             * Sorting by the cheapest unit price
             */
            $cheapestPriceIdQuery->orderBy('cheapestPrices.price');
        }

        $cheapestPriceIdQuery->setMaxResults(1);

        /*
         * Query to get the different price count
         */
        $countQuery->select('count(DISTINCT cheapestPrices.price)');
        $countQuery->from('s_articles_details', 'details');
        $countQuery->innerJoin('details', '(' . $countSubQuery->getSQL() . ')', 'cheapestPrices', $joinCondition);
        $countQuery->where('details.id = mainDetail.id');

        /*
         * Base query to get the cheapest price and different price count for each given variant
         */
        $baseQuery = $this->connection->createQueryBuilder();
        $baseQuery->select('mainDetail.ordernumber, (' . $cheapestPriceIdQuery->getSQL() . ') as id, ( ' . $countQuery->getSQL() . ' ) as different_price_count');
        $baseQuery->from('s_articles_details', 'mainDetail');
        $baseQuery->where('mainDetail.id IN (:variants)');

        return $baseQuery;
    }
}
