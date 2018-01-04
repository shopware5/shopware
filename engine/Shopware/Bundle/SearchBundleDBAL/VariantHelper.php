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
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomFacetGatewayInterface;
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
     * @var CustomFacetGatewayInterface
     */
    protected $customFacetGateway;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var ReflectionHelper
     */
    private $reflectionHelper;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var null|VariantFacet|bool
     */
    private $variantFacet;

    /**
     * @param Connection                  $connection
     * @param CustomListingHydrator       $customFacetGateway
     * @param FieldHelper                 $fieldHelper
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        Connection $connection,
        CustomListingHydrator $customFacetGateway,
        FieldHelper $fieldHelper,
        \Shopware_Components_Config $config)
    {
        $this->connection = $connection;
        $this->customFacetGateway = $customFacetGateway;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
        $this->reflectionHelper = new ReflectionHelper();
    }

    /**
     * @return bool|VariantFacet
     */
    public function getVariantFacet()
    {
        if ($this->variantFacet !== null) {
            return $this->variantFacet;
        }

        $this->variantFacet = false;

        $json = $this->connection->createQueryBuilder()
            ->addSelect('facet')
            ->from('s_search_custom_facet')
            ->where('unique_key = :key')
            ->andWhere('active = 1')
            ->setParameter('key', 'VariantFacet')
            ->execute()
            ->fetchColumn();

        if (empty($json)) {
            return $this->variantFacet;
        }

        $arr = json_decode($json, true);

        if (!empty($arr)) {
            $this->variantFacet = $this->reflectionHelper->createInstanceFromNamedArguments(key($arr), reset($arr));
        }

        return $this->variantFacet;
    }

    /**
     * @param QueryBuilder         $query
     * @param ShopContextInterface $context
     * @param Criteria             $criteria
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function joinPrices(QueryBuilder $query, ShopContextInterface $context, Criteria $criteria)
    {
        if ($query->hasState(self::VARIANT_LISTING_PRICE_JOINED)) {
            return;
        }

        error_log(print_r('join prices in variant helper', true) . "\n", 3, '/var/log/test.log');

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        foreach ($conditions as $condition) {
            $this->joinVariantCondition($query, $condition);
        }

        $variantCondition = [
            'listing_price.product_id = product.id',
        ];

        foreach ($conditions as $condition) {
            $tableKey = $condition->getName();
            $variantCondition[] = 'listing_price.' . $tableKey . '_id = ' . $tableKey . '.option_id';
        }

        $priceTable = $this->createListingPriceTable($criteria);
        $this->joinPriceGroup($query);
        $query->addSelect(['listing_price.*']);
        $query->addSelect('MIN(' . $this->getSelection($context) . ') as cheapest_price_value');

        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');
        $query->innerJoin('variant', '(' . $priceTable->getSQL() . ')', 'listing_price', implode(' AND ', $variantCondition));

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        $query->addState(self::VARIANT_LISTING_PRICE_JOINED);
    }

    /**
     * @param QueryBuilder $query
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
     * @param QueryBuilder     $query
     * @param VariantCondition $condition
     *
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function joinVariantCondition(QueryBuilder $query, VariantCondition $condition)
    {
        /** @var VariantCondition $condition */
        $tableKey = $condition->getName();

        $suffix = crc32(json_encode($condition));

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
     * @param Criteria $criteria
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function createListingPriceTable(Criteria $criteria)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'MIN(price) AS price',
            'prices.articledetailsID AS variant_id',
            'prices.articleID AS product_id',
        ]);
        $query->from('s_articles_prices', 'prices');

        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        /** @var ConditionInterface $condition */
        foreach ($conditions as $condition) {
            $tableKey = $condition->getName();
            $column = $tableKey . '.option_id AS ' . $tableKey . '_id';
            $query->innerJoin('prices', 's_article_configurator_option_relations', $tableKey, $tableKey . '.article_id = prices.articledetailsID');
            $query->addSelect($column);
        }

        $query->groupBy('prices.articleID');

        return $query;
    }

    /**
     * @param ShopContextInterface $context
     *
     * @return string
     */
    private function getSelection(ShopContextInterface $context)
    {
        $current = $context->getCurrentCustomerGroup();
        $currency = $context->getCurrency();

        $discount = $current->useDiscount() ? $current->getPercentageDiscount() : 0;

        $considerMinPurchase = $this->config->get('calculateCheapestPriceWithMinPurchase');

        $taxCase = $this->buildTaxCase($context);

        // Rounded to filter this value correctly
        // => 2,99999999 displayed as 3,- € but won't be displayed with a filter on price >= 3,- €
        $selection = 'ROUND(' .

            // Customer group price (with fallback switch)
            'listing_price.price' .

            // Multiplied with the variant min purchase
            ($considerMinPurchase ? ' * variant.minpurchase' : '') .

            // Multiplied with the percentage price group discount
            ' * ((100 - IFNULL(priceGroup.discount, 0)) / 100)' .

            // Multiplied with the product tax if the current customer group should see gross prices
            ($current->displayGrossPrices() ? ' * (( ' . $taxCase . ' + 100) / 100)' : '') .

            // Multiplied with the percentage discount of the current customer group
            ($discount ? ' * ' . (100 - (float) $discount) / 100 : '') .

            // Multiplied with the shop currency factor
            ($currency->getFactor() ? ' * ' . $currency->getFactor() : '') .

            ', 2)';

        return $selection;
    }

    /**
     * Builds the tax cases for the price selection query
     *
     * @param ShopContextInterface $context
     *
     * @return string
     */
    private function buildTaxCase(ShopContextInterface $context)
    {
        $cases = [];
        foreach ($context->getTaxRules() as $rule) {
            $cases[] = ' WHEN ' . $rule->getId() . ' THEN ' . $rule->getTax();
        }

        return '(CASE tax.id ' . implode(' ', $cases) . ' END)';
    }

    /**
     * @return array
     */
    private function getPriceColumns()
    {
        return [
            '`id`',
            '`pricegroup`',
            '`from`',
            '`to`',
            '`articleID`',
            '`articledetailsID`',
            '`price`',
            '`pseudoprice`',
            '`baseprice`',
            '`percent`',
        ];
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     */
    private function joinPriceGroup(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $discountStart = '1';
        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $discountStart = '(SELECT MAX(discountstart) FROM s_core_pricegroups_discounts subPriceGroup WHERE subPriceGroup.id = priceGroup.id AND subPriceGroup.customergroupID = :priceGroupCustomerGroup)';
        }

        $query->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = ' . $discountStart . '
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );
    }

    /**
     * @param ShopContextInterface $context
     *
     * @return bool
     */
    private function hasDifferentCustomerGroups(ShopContextInterface $context)
    {
        return $context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId();
    }
}
