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

namespace Shopware\Bundle\SearchBundleDBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomListingHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\ReflectionHelper;

class VariantHelper implements VariantHelperInterface
{
    const VARIANTS_JOINED = 'all_variants';
    const VARIANT_LISTING_PRICE_JOINED = 'variant_listing_price';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CustomListingHydrator
     */
    protected $customFacetGateway;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var VariantFacet|bool|null
     */
    protected $variantFacet = false;

    /**
     * @var ReflectionHelper
     */
    protected $reflectionHelper;

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    /**
     * @var ListingPriceHelper
     */
    protected $listingPriceHelper;

    public function __construct(
        Connection $connection,
        CustomListingHydrator $customFacetGateway,
        FieldHelper $fieldHelper,
        \Shopware_Components_Config $config,
        ListingPriceHelper $listingPriceHelper
    ) {
        $this->connection = $connection;
        $this->customFacetGateway = $customFacetGateway;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
        $this->reflectionHelper = new ReflectionHelper();
        $this->listingPriceHelper = $listingPriceHelper;
    }

    /**
     * @throws \ReflectionException
     *
     * @return VariantFacet|null
     */
    public function getVariantFacet()
    {
        if ($this->variantFacet !== false) {
            return $this->variantFacet;
        }

        $json = $this->connection->createQueryBuilder()
            ->addSelect('facet')
            ->from('s_search_custom_facet')
            ->where('unique_key = :key')
            ->andWhere('active = 1')
            ->setParameter('key', 'VariantFacet')
            ->execute()
            ->fetchColumn();

        if (empty($json)) {
            return $this->variantFacet = null;
        }

        $arr = json_decode($json, true);

        if (empty($arr)) {
            return $this->variantFacet = null;
        }

        /** @var \Shopware\Bundle\SearchBundle\Facet\VariantFacet|null variantFacet */
        $variantFacet = $this->reflectionHelper->createInstanceFromNamedArguments(key($arr), reset($arr));
        $this->variantFacet = $variantFacet;

        return $this->variantFacet;
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function joinPrices(QueryBuilder $query, ShopContextInterface $context, Criteria $criteria)
    {
        if ($this->config->get('hideNoInStock')) {
            $this->joinListingPrices($query, $context, $criteria);
        } else {
            $this->joinSalePrices($query, $context, $criteria);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function joinVariants(QueryBuilder $query)
    {
        if ($query->hasState(self::VARIANTS_JOINED)) {
            return;
        }

        $query->innerJoin(
            'product',
            's_articles_details',
            'allVariants',
            'allVariants.articleID = product.id AND allVariants.active = 1'
        );

        $query->addState(self::VARIANTS_JOINED);
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function joinVariantCondition(QueryBuilder $query, VariantCondition $condition)
    {
        /** @var VariantCondition $condition */
        $tableKey = $condition->getName();

        $suffix = md5(json_encode($condition));

        if ($query->hasState('option_' . $tableKey)) {
            return;
        }

        $query->addState('option_' . $tableKey);

        $where = [];

        /** @var VariantCondition $condition */
        foreach ($condition->getOptionIds() as $valueId) {
            $valueKey = ':' . $tableKey . '_' . $valueId . '_' . $suffix;
            $where[] = $tableKey . '.option_id = ' . $valueKey;
            $query->setParameter($valueKey, $valueId);
        }

        $where = implode(' OR ', $where);

        $query->innerJoin(
            'variant',
            's_article_configurator_option_relations',
            $tableKey,
            'variant.id = ' . $tableKey . '.article_id
             AND (' . $where . ')'
        );

        if (!$condition->expandVariants()) {
            return;
        }

        if (!$query->hasState('variant_group_by')) {
            $query->resetQueryPart('groupBy');
        }

        $query->addState('variant_group_by');
        $query->addGroupBy($tableKey . '.option_id');
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function joinListingPrices(QueryBuilder $query, ShopContextInterface $context, Criteria $criteria)
    {
        if ($query->hasState(self::VARIANT_LISTING_PRICE_JOINED)) {
            return;
        }

        $variantCondition = [
            'listing_price.product_id = variant.articleId',
        ];

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        /** @var VariantCondition $condition */
        foreach ($conditions as $condition) {
            if ($condition->expandVariants()) {
                $this->joinVariantCondition($query, $condition);

                $tableKey = $condition->getName();
                $variantCondition[] = 'listing_price.' . $tableKey . '_id = ' . $tableKey . '.option_id';
            }
        }

        $priceTable = $this->createListingPriceTable($criteria, $context);

        $query->addSelect('listing_price.*');
        $query->leftJoin('variant', '(' . $priceTable->getSQL() . ')', 'listing_price', implode(' AND ', $variantCondition));

        $query->andWhere('variant.laststock * variant.instock >= variant.laststock * variant.minpurchase');

        $query->andWhere('variant.active = 1');

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $query->addState(self::VARIANT_LISTING_PRICE_JOINED);
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function joinSalePrices(QueryBuilder $query, ShopContextInterface $context, Criteria $criteria)
    {
        if ($query->hasState(self::VARIANT_LISTING_PRICE_JOINED)) {
            return;
        }

        $subQuery = new QueryBuilder($this->connection);
        $subQuery->from('s_articles_details', 'variant');

        $variantCondition = [
            'listing_price.product_id = variant.articleId',
        ];

        $variantOnSaleCondition = [
            'onsale_listing_price.product_id = variant.articleId',
        ];

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        /** @var VariantCondition $condition */
        foreach ($conditions as $condition) {
            if ($condition->expandVariants()) {
                $this->joinVariantCondition($query, $condition);
                $this->joinVariantCondition($subQuery, $condition);

                $tableKey = $condition->getName();
                $variantCondition[] = 'listing_price.' . $tableKey . '_id = ' . $tableKey . '.option_id';
                $variantOnSaleCondition[] = 'onsale_listing_price.' . $tableKey . '_id = ' . $tableKey . '.option_id';
                $subQuery->addSelect('IFNULL(listing_price.' . $tableKey . '_id, onsale_listing_price.' . $tableKey . '_id) AS ' . $tableKey . '_id');
            }
        }

        $priceTable = $this->createListingPriceTable($criteria, $context);
        $onSalePriceTable = $this->createOnSaleListingPriceTable($criteria, $context);

        $subQuery->addSelect([$this->getOnSalePriceColums()]);
        $subQuery->addSelect([
            'IFNULL(listing_price.cheapest_price, onsale_listing_price.cheapest_price) AS cheapest_price',
            'IFNULL(listing_price.variant_id, onsale_listing_price.variant_id) AS variant_id',
            'IFNULL(listing_price.different_price_count, onsale_listing_price.different_price_count) AS different_price_count',
            'IFNULL(listing_price.product_id, onsale_listing_price.product_id) AS product_id',
        ]);

        $subQuery->leftJoin('variant', '(' . $priceTable->getSQL() . ')', 'listing_price', implode(' AND ', $variantCondition));
        $subQuery->leftJoin('variant', '(' . $onSalePriceTable->getSQL() . ')', 'onsale_listing_price', implode(' AND ', $variantOnSaleCondition));
        $subQuery->resetQueryPart('groupBy');

        $query->addSelect('listing_price.*');
        $query->leftJoin('variant', '(' . $subQuery->getSQL() . ')', 'listing_price', implode(' AND ', $variantCondition));

        $query->andWhere('variant.active = 1');

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $query->addState(self::VARIANT_LISTING_PRICE_JOINED);
    }

    /**
     * Returns the price columns for the onsale option.
     *
     * @return string
     */
    protected function getOnSalePriceColums()
    {
        $template = 'IFNULL(listing_price.%s, onsale_listing_price.%s) %s';

        return implode(',',
            array_map(
                function ($column) use ($template) {
                    return sprintf($template, $column, $column, $column);
                }, $this->listingPriceHelper->getPriceColumns()
            )
        );
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createListingPriceTable(Criteria $criteria, ShopContextInterface $context)
    {
        $selection = $this->listingPriceHelper->getSelection($context);

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'prices.*',
            'MIN(' . $selection . ') AS cheapest_price',
            'COUNT(DISTINCT price) as different_price_count',
        ]);

        $priceTable = $this->listingPriceHelper->getPriceTable($context);
        $priceTable->innerJoin('defaultPrice', 's_articles_details', 'details', 'details.id = defaultPrice.articledetailsID');
        $priceTable->andWhere('(details.laststock * details.instock) >= (details.laststock * details.minpurchase)');

        $query->from('s_articles', 'product');
        $query->innerJoin('product', '(' . $priceTable->getSQL() . ')', 'prices', 'product.id = prices.articleID');
        $query->innerJoin('prices', 's_articles_details', 'variant', 'variant.id = prices.articledetailsID AND variant.active = 1');
        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');

        $this->joinAvailableVariant($query);
        $query->andWhere('prices.articledetailsID = availableVariant.id');

        $this->listingPriceHelper->joinPriceGroup($query);

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        /** @var VariantCondition $condition */
        foreach ($conditions as $condition) {
            if (!$condition->expandVariants()) {
                continue;
            }

            $tableKey = $condition->getName();
            $column = $tableKey . '.option_id AS ' . $tableKey . '_id';
            $query->innerJoin('prices', 's_article_configurator_option_relations', $tableKey, $tableKey . '.article_id = prices.articledetailsID');
            $query->addSelect($column);
            $query->addGroupBy($tableKey . '.option_id');
        }

        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $query->andWhere("IF(priceGroup.id IS NOT NULL, prices.from = 1, prices.to = 'beliebig')");
        } else {
            $query->andWhere('prices.from = 1');
        }

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $query->addGroupBy('prices.articleID');

        return $query;
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createOnSaleListingPriceTable(Criteria $criteria, ShopContextInterface $context)
    {
        $selection = $this->listingPriceHelper->getSelection($context);

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'prices.*',
            'MAX(' . $selection . ') AS cheapest_price',
            'COUNT(DISTINCT price) as different_price_count',
        ]);

        $priceTable = $this->listingPriceHelper->getPriceTable($context);

        $query->from('s_articles', 'product');
        $query->innerJoin('product', '(' . $priceTable->getSQL() . ')', 'prices', 'product.id = prices.articleID');
        $query->innerJoin('prices', 's_articles_details', 'variant', 'variant.id = prices.articledetailsID AND variant.active = 1');
        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');

        $this->joinAvailableVariant($query);
        $query->andWhere('prices.articledetailsID = availableVariant.id');

        $this->listingPriceHelper->joinPriceGroup($query);

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        /** @var VariantCondition $condition */
        foreach ($conditions as $condition) {
            if (!$condition->expandVariants()) {
                continue;
            }

            $tableKey = $condition->getName();
            $column = $tableKey . '.option_id AS ' . $tableKey . '_id';
            $query->innerJoin('prices', 's_article_configurator_option_relations', $tableKey, $tableKey . '.article_id = prices.articledetailsID');
            $query->addSelect($column);
            $query->addGroupBy($tableKey . '.option_id');
        }

        $query->andWhere('prices.from = 1');

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $query->addGroupBy('prices.articleID');

        return $query;
    }

    /**
     * @return bool
     */
    protected function hasDifferentCustomerGroups(ShopContextInterface $context)
    {
        return $context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId();
    }

    protected function joinAvailableVariant(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $stockCondition = '';
        if ($this->config->get('hideNoInstock')) {
            $stockCondition = 'AND (availableVariant.laststock * availableVariant.instock) >= (availableVariant.laststock * availableVariant.minpurchase)';
        }

        $query->innerJoin(
            'product',
            's_articles_details',
            'availableVariant',
            'availableVariant.articleID = product.id
             AND availableVariant.active = 1 ' . $stockCondition
        );
    }
}
