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

namespace Shopware\Components\Cart\Struct;

class Price
{
    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $netPrice;

    /**
     * @var float
     */
    private $taxRate;

    /**
     * @var float
     */
    private $tax;

    /**
     * @param float      $price
     * @param float      $netPrice
     * @param float      $taxRate
     * @param float|null $tax
     */
    public function __construct($price, $netPrice, $taxRate, $tax)
    {
        $this->price = $price;
        $this->netPrice = $netPrice;
        $this->taxRate = $taxRate;
        $this->tax = $tax;
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
