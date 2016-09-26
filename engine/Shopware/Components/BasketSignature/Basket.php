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

class Basket implements JsonSerializable
{
    /**
     * @var BasketItem[]
     */
    public $items = [];

    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $taxAmount;

    /**
     * Basket constructor.
     *
     * @param float $amount
     * @param float $taxAmount
     * @param BasketItem[] $items
     */
    public function __construct($amount, $taxAmount, array $items)
    {
        $this->items = $items;
        $this->amount = $amount;
        $this->taxAmount = $taxAmount;
    }

    /**
     * Creates a new instance which contains all relevant signature data.
     * The provided data array are based on the data of @see \sBasket::sGetBasket
     *
     * @param array $data
     * @return Basket
     */
    public static function createFromSBasket(array $data)
    {
        return new self(
            (float) $data['sAmount'],
            (float) $data['sAmountTax'],
            array_map([BasketItem::class, 'createFromSBasket'], $data['content'])
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
