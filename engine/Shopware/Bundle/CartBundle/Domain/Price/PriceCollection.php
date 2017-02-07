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

namespace Shopware\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Collection;

class PriceCollection extends Collection
{
    /**
     * @var Price[]
     */
    protected $items = [];

    /**
     * @param Price $price
     */
    public function add($price)
    {
        $this->items[] = $price;
    }

    /**
     * @return TaxRuleCollection
     */
    public function getTaxRules()
    {
        $rules = new TaxRuleCollection();
        foreach ($this->items as $price) {
            $rules = $rules->merge($price->getTaxRules());
        }
        return $rules;
    }

    /**
     * Sum of all total prices
     * @return float
     */
    private function getAmount()
    {
        $prices = $this->map(function (Price $price) {
            return $price->getPrice();
        });
        return array_sum($prices);
    }

    /**
     * @return Price
     */
    public function getTotalPrice()
    {
        $amount = $this->getAmount();
        return new Price(
            $amount,
            $amount,
            $this->getCalculatedTaxes(),
            $this->getTaxRules()
        );
    }

    /**
     * @return CalculatedTaxCollection
     */
    public function getCalculatedTaxes()
    {
        $taxes = new CalculatedTaxCollection();
        foreach ($this->items as $price) {
            $taxes = $taxes->merge($price->getCalculatedTaxes());
        }
        return $taxes;
    }
}
