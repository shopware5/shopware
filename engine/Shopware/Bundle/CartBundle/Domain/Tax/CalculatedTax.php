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

namespace Shopware\Bundle\CartBundle\Domain\Tax;

class CalculatedTax
{
    /**
     * @var float
     */
    protected $tax = 0;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @var float
     */
    protected $price = 0;

    /**
     * @param float $tax
     * @param float $taxRate
     * @param float $price
     */
    public function __construct($tax, $taxRate, $price)
    {
        $this->tax = $tax;
        $this->taxRate = $taxRate;
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @param CalculatedTax $calculatedTax
     */
    public function increment(CalculatedTax $calculatedTax)
    {
        $this->tax += $calculatedTax->getTax();
        $this->price += $calculatedTax->getPrice();
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
}
