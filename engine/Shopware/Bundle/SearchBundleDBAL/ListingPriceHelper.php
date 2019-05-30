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
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ListingPriceHelper
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(Connection $connection, \Shopware_Components_Config $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getSelection(ShopContextInterface $context)
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
            'prices.price' .

            // Multiplied with the variant min purchase
            ($considerMinPurchase ? ' * availableVariant.minpurchase' : '') .

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
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getPriceTable(ShopContextInterface $context)
    {
        $priceTable = $this->connection->createQueryBuilder();
        $priceTable->select($this->getDefaultPriceColumns());
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

    public function joinPriceGroup(\Doctrine\DBAL\Query\QueryBuilder $query)
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
     * @return array
     */
    public function getPriceColumns()
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
     * @return string
     */
    private function getPriceSwitchColumns()
    {
        $template = 'IFNULL(customerPrice.%s, defaultPrice.%s) as %s';
        $switch = [];
        foreach ($this->getPriceColumns() as $column) {
            $switch[] = sprintf($template, $column, $column, $column);
        }

        $switch[] = 'defaultPrice.articleID as product_id';
        $switch[] = 'defaultPrice.articledetailsID as variant_id';

        return implode(',', $switch);
    }

    /**
     * Get the columns for the price group prices.
     *
     * @return string
     */
    private function getDefaultPriceColumns()
    {
        $template = 'defaultPrice.%s';
        $switch = [];
        foreach ($this->getPriceColumns() as $column) {
            $switch[] = sprintf($template, $column);
        }
        $switch[] = sprintf($template, 'articleID as product_id');
        $switch[] = sprintf($template, 'articledetailsID as variant_id');

        return implode(',', $switch);
    }

    /**
     * @return bool
     */
    private function hasDifferentCustomerGroups(ShopContextInterface $context)
    {
        return $context->getCurrentCustomerGroup()->getId() !== $context->getFallbackCustomerGroup()->getId();
    }

    /**
     * Builds the tax cases for the price selection query
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
}
