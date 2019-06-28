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

namespace Shopware\Components\Cart;

use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\Cart\Struct\Price;

class BasketHelper implements BasketHelperInterface
{
    /**
     * @var ProportionalTaxCalculatorInterface
     */
    private $calculator;

    /**
     * @var BasketQueryHelperInterface
     */
    private $basketQueryHelper;

    public function __construct(
        ProportionalTaxCalculatorInterface $calculator,
        BasketQueryHelperInterface $basketQueryHelper
    ) {
        $this->basketQueryHelper = $basketQueryHelper;
        $this->calculator = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public function addProportionalDiscount(DiscountContext $discountContext)
    {
        $prices = $this->getPositionPrices($discountContext);
        $hasMultipleTaxes = $this->calculator->hasDifferentTaxes($prices);

        if ($discountContext->getDiscountType() === self::DISCOUNT_ABSOLUTE) {
            $discounts = $this->calculator->calculate(
                $discountContext->getDiscountValue(),
                $prices,
                $discountContext->isNetPrice()
            );
        } else {
            $discounts = $this->calculator->recalculatePercentageDiscount(
                $discountContext->getDiscountValue(),
                $prices,
                $discountContext->isNetPrice()
            );
        }

        $discountBaseName = $discountContext->getDiscountName();
        /** @var Price $discount */
        foreach ($discounts as $discount) {
            $discountContext->setPrice($discount);
            $discountContext->setDiscountName(
                $discountBaseName . ($hasMultipleTaxes ? ' (' . $discount->getTaxRate() . '%)' : '')
            );

            $query = $this->basketQueryHelper->getInsertDiscountQuery($discountContext);
            $query->execute();

            $discountContext->setBasketId($this->basketQueryHelper->getLastInsertId());

            $query = $this->basketQueryHelper->getInsertDiscountAttributeQuery($discountContext);
            $query->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPositionPrices(DiscountContext $discountContext)
    {
        $query = $this->basketQueryHelper->getPositionPricesQuery(
            $discountContext
        );

        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Price(
                (float) $row['end_price'] * $row['quantity'],
                (float) $row['net_price'] * $row['quantity'],
                (float) $row['tax_rate'],
                null
            );
        }, $rows);
    }
}
