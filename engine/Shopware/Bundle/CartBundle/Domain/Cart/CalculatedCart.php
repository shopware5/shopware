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

    public function getName(): string
    {
        return $this->cart->getName();
    }

    public function getPrice(): CartPrice
    {
        return $this->price;
    }

    public function getToken(): string
    {
        return $this->cart->getToken();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getLineItems(): CalculatedLineItemCollection
    {
        return $this->lineItems;
    }

    public function getDeliveries(): DeliveryCollection
    {
        return $this->deliveries;
    }
}
