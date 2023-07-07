<?php

declare(strict_types=1);
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

namespace Shopware\Components\Cart\Struct;

class Price
{
    private float $price;

    private float $netPrice;

    private float $taxRate;

    private float $tax;

    /**
     * @param float|numeric-string      $price
     * @param float|numeric-string      $netPrice
     * @param float|numeric-string      $taxRate
     * @param float|numeric-string|null $tax
     */
    public function __construct($price, $netPrice, $taxRate, $tax)
    {
        $this->price = (float) $price;
        $this->netPrice = (float) $netPrice;
        $this->taxRate = (float) $taxRate;
        $this->tax = (float) $tax;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }
}
