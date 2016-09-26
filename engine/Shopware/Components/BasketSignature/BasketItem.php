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

namespace Shopware\Components\BasketSignature;

use JsonSerializable;

class BasketItem implements JsonSerializable
{
    /**
     * @var string
     */
    public $number;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $taxRate;

    /**
     * @var float
     */
    public $price;

    /**
     * BasketItem constructor.
     *
     * @param string $number
     * @param int $quantity
     * @param float $taxRate
     * @param float $price
     */
    public function __construct($number, $quantity, $taxRate, $price)
    {
        $this->number = $number;
        $this->quantity = $quantity;
        $this->taxRate = $taxRate;
        $this->price = $price;
    }

    /**
     * Creates a new instance which contains all relevant signature data of an basket row.
     * The provided data array are based on the data of @see \sBasket::sGetBasket
     *
     * @param array $item
     * @return BasketItem
     */
    public static function createFromSBasket(array $item)
    {
        return new self(
            $item['ordernumber'],
            (int) $item['quantity'],
            (float) $item['tax_rate'],
            (float) $item['price']
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
