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
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ListingPriceTable implements ListingPriceTableInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param Connection $connection
     * @param \Shopware_Components_Config $config
     */
    public function __construct(Connection $connection, \Shopware_Components_Config $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * @param ShopContextInterface $context
     * @return DoctrineQueryBuilder
     */
    public function get(ShopContextInterface $context)
    {
        $priceTable = $this->getPriceTable($context);

        $query = $this->connection->createQueryBuilder();

        $selection = 'MIN(' . $this->getSelection($context) . ') as cheapest_price';

        $query->select(['prices.*', $selection]);
        $query->from('s_articles', 'product');
        $query->innerJoin('product', 's_core_tax', 'tax', 'tax.id = product.taxID');
        $query->innerJoin('product', '('. $priceTable->getSQL() .')', 'prices', 'product.id = prices.articleID');

        $this->joinAvailableVariant($query);
        $this->joinPriceGroup($query);

        $query->andWhere('prices.articledetailsID = availableVariant.id');

        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $query->andWhere("IF(priceGroup.id IS NOT NULL, prices.from = 1, prices.to = 'beliebig')");
        } else {
            $query->andWhere('prices.from = 1');
        }

        $query->groupBy('product.id');

        $query->setParameter(':fallbackCustomerGroup', $context->getFallbackCustomerGroup()->getKey());
        $query->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        if ($this->hasDifferentCustomerGroups($context)) {
            $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey());
        }

        return $query;
    }

    /**
     * @param ShopContextInterface $context
     * @return bool
     */
    private function hasDifferentCustomerGroups(ShopContextInterface $context)
    {
        return $context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId() ;
    }

    /**
     * @return string
     */
    private function getPriceSwitchColumns()
    {
        $template = 'IFNULL(customerPrice.%s, defaultPrice.%s) as %s';
        $switch = [];
        foreach ($this->getPriceColumns() as $column) {
            $switch[] = sprintf($template, $column, $column, $column);
        }
        return implode(',', $switch);
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
            '`percent`'
        ];
    }

    /**
     * @param ShopContextInterface $context
     * @return string
     */
    private function getSelection(ShopContextInterface $context)
    {
        $current  = $context->getCurrentCustomerGroup();
        $currency = $context->getCurrency();

        $discount = $current->useDiscount() ? $current->getPercentageDiscount() : 0;

        $considerMinPurchase = $this->config->get('calculateCheapestPriceWithMinPurchase');

        $taxCase = $this->buildTaxCase($context);

        //rounded to filter this value correctly
        // => 2,99999999 displayed as 3,- € but won't be displayed with a filter on price >= 3,- €
        $selection = 'ROUND(' .

            //customer group price (with fallback switch)
            'prices.price' .

            //multiplied with the variant min purchase
            ($considerMinPurchase ? ' * availableVariant.minpurchase' : '') .

            //multiplied with the percentage price group discount
            ' * ((100 - IFNULL(priceGroup.discount, 0)) / 100)' .

            //multiplied with the product tax if the current customer group should see gross prices
            ($current->displayGrossPrices() ? " * (( ".$taxCase." + 100) / 100)" : '') .

            //multiplied with the percentage discount of the current customer group
            ($discount ? " * " . (100 - (float) $discount) / 100 : '') .

            //multiplied with the shop currency factor
            ($currency->getFactor() ? " * " . $currency->getFactor() : '') .

            ', 2)';

        return $selection;
    }

    /**
     * Builds the tax cases for the price selection query
     * @param ShopContextInterface $context
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
     * @param ShopContextInterface $context
     * @return DoctrineQueryBuilder
     */
    private function getPriceTable(ShopContextInterface $context)
    {
        $priceTable = $this->connection->createQueryBuilder();
        $priceTable->select($this->getPriceColumns());
        $priceTable->from('s_articles_prices', 'defaultPrice');
        $priceTable->where('defaultPrice.pricegroup = :fallbackCustomerGroup');

        if (!$this->hasDifferentCustomerGroups($context)) {
            return $priceTable;
        }

        $priceTable->select($this->getPriceSwitchColumns());
        $priceTable->leftJoin(
            'defaultPrice',
            's_articles_prices',
            'customerPrice',
            'customerPrice.articledetailsID = defaultPrice.articledetailsID
            AND customerPrice.pricegroup = :currentCustomerGroup'
        );


        return $priceTable;
    }

    /**
     * @param DoctrineQueryBuilder $query
     */
    private function joinAvailableVariant(DoctrineQueryBuilder $query)
    {
        $stockCondition = '';
        if ($this->config->get('hideNoInstock')) {
            $stockCondition = 'AND (product.laststock * availableVariant.instock) >= (product.laststock * availableVariant.minpurchase)';
        }

        $query->innerJoin(
            'product',
            's_articles_details',
            'availableVariant',
            'availableVariant.articleID = product.id
             AND availableVariant.active = 1 ' . $stockCondition
        );
    }

    /**
     * @param DoctrineQueryBuilder $query
     */
    private function joinPriceGroup(DoctrineQueryBuilder $query)
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
}
