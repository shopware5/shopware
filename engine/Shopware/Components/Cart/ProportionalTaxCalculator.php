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

use Shopware\Components\Cart\Struct\Price;

class ProportionalTaxCalculator implements ProportionalTaxCalculatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function calculate($discount, $prices, $isNetPrice)
    {
        $sumByTaxes = $this->sumByTax($prices);

        $total = array_sum($sumByTaxes);

        $taxes = [];

        foreach ($sumByTaxes as $taxRate => $price) {
            $proportion = $price / $total * 100;

            $priceForTax = $discount / 100 * $proportion;

            $net = $priceForTax / ((100 + $taxRate) / 100);

            $tax = $net * ($taxRate / 100);

            $taxes[] = new Price($isNetPrice ? $net : $priceForTax, $net, $taxRate, $tax);
        }

        return $taxes;
    }

    /**
     * {@inheritdoc}
     */
    public function recalculatePercentageDiscount($percentage, array $prices, $isNetPrice)
    {
        $discounts = [];
        /** @var Price $price */
        foreach ($prices as $price) {
            $key = md5((string) $price->getTaxRate());

            if (!array_key_exists($key, $discounts)) {
                $discounts[$key] = [
                     'price' => 0,
                     'netPrice' => 0,
                     'taxRate' => $price->getTaxRate(),
                 ];
            }

            $newPrice = round($price->getPrice() * ($percentage / 100), 2);

            $newNet = $newPrice / ((100 + $price->getTaxRate()) / 100);

            $discounts[$key]['price'] += $newPrice;
            $discounts[$key]['netPrice'] += $newNet;
        }

        return array_map(function ($item) use ($isNetPrice) {
            return new Price($item['price'], $isNetPrice ? $item['price'] : $item['netPrice'], $item['taxRate'], null);
        }, $discounts);
    }

    /**
     * {@inheritdoc}
     */
    public function hasDifferentTaxes(array $prices)
    {
        $taxes = array_map(function (Price $price) {
            return $price->getTaxRate();
        }, $prices);

        return count(array_unique($taxes)) > 1;
    }

    /**
     * @param Price[] $prices
     *
     * @return array
     */
    protected function sumByTax(array $prices)
    {
        $sum = [];
        foreach ($prices as $price) {
            $key = (string) $price->getTaxRate();

            if (array_key_exists($key, $sum)) {
                $taxPrice = $sum[$key];
            } else {
                $taxPrice = 0;
            }
            $taxPrice += $price->getNetPrice();

            $sum[$key] = $taxPrice;
        }

        return $sum;
    }
}
