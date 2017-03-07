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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;

class CalculatedCart implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var CartPrice
     */
    protected $price;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CalculatedLineItemCollection
     */
    protected $lineItems;

    /**
     * @var DeliveryCollection
     */
    protected $deliveries;

    /**
     * @param Cart                         $cart
     * @param CalculatedLineItemCollection $lineItems
     * @param CartPrice                    $price
     * @param DeliveryCollection           $deliveries
     */
    public function __construct(
        Cart $cart,
        CalculatedLineItemCollection $lineItems,
        CartPrice $price,
        DeliveryCollection $deliveries
    ) {
        $this->cart = $cart;
        $this->lineItems = $lineItems;
        $this->price = $price;
        $this->deliveries = $deliveries;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->cart->getName();
    }

    /**
     * @return CartPrice
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->cart->getToken();
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return CalculatedLineItemCollection
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @return DeliveryCollection
     */
    public function getDeliveries()
    {
        return $this->deliveries;
    }
}
