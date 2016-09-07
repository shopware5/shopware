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
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceHelper implements PriceHelperInterface
{
    const STATE_INCLUDES_CHEAPEST_PRICE = 'cheapest_price';

    const STATE_INCLUDES_DEFAULT_PRICE = 'default_price';

    const STATE_INCLUDES_AVAILABLE_VARIANT = 'available_variant';

    const contextError = 'Expected Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface instead of Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface. This will be changed in \Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface in SW 5.2';

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
     * @inheritdoc
     */
    public function getSelection(Struct\ShopContextInterface $context)
    {
        if (!$context instanceof Struct\ProductContextInterface) {
            throw new \InvalidArgumentException(self::contextError);
        }

        $fallback = $context->getFallbackCustomerGroup();
        $current  = $context->getCurrentCustomerGroup();
        $currency = $context->getCurrency();

        $priceField = 'defaultPrice.price';
        if ($fallback->getId() != $current->getId()) {
            $priceField = 'IFNULL(customerPrice.price, defaultPrice.price)';
        }

        $discount = $current->useDiscount() ? $current->getPercentageDiscount() : 0;

        $considerMinPurchase = $this->config->get('calculateCheapestPriceWithMinPurchase');

        $taxCase = $this->buildTaxCase($context);

        //rounded to filter this value correctly
        // => 2,99999999 displayed as 3,- € but won't be displayed with a filter on price >= 3,- €
        return 'ROUND(' .

            //customer group price (with fallback switch)
            $priceField .

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
    }

    /**
     * Builds the tax cases for the price selection query
     * @param Struct\ProductContextInterface $context
     * @return string
     */
    private function buildTaxCase(Struct\ProductContextInterface $context)
    {
        $cases = [];
        foreach ($context->getTaxRules() as $rule) {
            $cases[] = ' WHEN ' . $rule->getId() . ' THEN ' . $rule->getTax();
        }
        return '(CASE tax.id ' . implode(' ', $cases) . ' END)';
    }

    /**
     * @inheritdoc
     */
    public function joinPrices(
        QueryBuilder $query,
        Struct\ShopContextInterface $context
    ) {
        if ($query->hasState(self::STATE_INCLUDES_CHEAPEST_PRICE)) {
            return;
        }

        $this->joinDefaultPrices($query, $context);

        $graduation = 'customerPrice.from = 1';
        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $graduation = "customerPrice.to = 'beliebig'";
        }

        $query->leftJoin(
            'product',
            's_articles_prices',
            'customerPrice',
            'customerPrice.articleID = product.id
             AND customerPrice.pricegroup = :currentCustomerGroup
             AND ' . $graduation . '
             AND availableVariant.id = customerPrice.articledetailsID'
        );

        $query->leftJoin(
            'product',
            's_core_pricegroups_discounts',
            'priceGroup',
            'priceGroup.groupID = product.pricegroupID
             AND priceGroup.discountstart = 1
             AND priceGroup.customergroupID = :priceGroupCustomerGroup
             AND product.pricegroupActive = 1'
        );

        $query->setParameter(':currentCustomerGroup', $context->getCurrentCustomerGroup()->getKey())
            ->setParameter(':priceGroupCustomerGroup', $context->getCurrentCustomerGroup()->getId());

        $query->addState(self::STATE_INCLUDES_CHEAPEST_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function joinDefaultPrices(QueryBuilder $query, Struct\ShopContextInterface $context)
    {
        if ($query->hasState(self::STATE_INCLUDES_DEFAULT_PRICE)) {
            return;
        }

        $this->joinAvailableVariant($query);

        $graduation = 'defaultPrice.from = 1';
        if ($this->config->get('useLastGraduationForCheapestPrice')) {
            $graduation = "defaultPrice.to = 'beliebig'";
        }

        $query->leftJoin(
            'product',
            's_articles_prices',
            'defaultPrice',
            'defaultPrice.articledetailsID = availableVariant.id
             AND defaultPrice.pricegroup = :fallbackCustomerGroup
             AND ' . $graduation
        );

        $query->setParameter(
            ':fallbackCustomerGroup',
            $context->getFallbackCustomerGroup()->getKey()
        );

        $query->addState(self::STATE_INCLUDES_DEFAULT_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function joinAvailableVariant(QueryBuilder $query)
    {
        if ($query->hasState(self::STATE_INCLUDES_AVAILABLE_VARIANT)) {
            return;
        }

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

        $query->addState(self::STATE_INCLUDES_AVAILABLE_VARIANT);
    }
}
