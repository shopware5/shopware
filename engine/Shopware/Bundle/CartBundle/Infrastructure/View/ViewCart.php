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

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;

class ViewCart implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var ViewLineItemCollection
     */
    protected $lineItems;

    /**
     * @var CalculatedCart
     */
    protected $calculatedCart;

    final private function __construct(CalculatedCart $calculatedCart)
    {
        $this->calculatedCart = $calculatedCart;
        $this->lineItems = new ViewLineItemCollection([]);
    }

    public static function createFromCalculatedCart(CalculatedCart $calculatedCart): ViewCart
    {
        return new self($calculatedCart);
    }

    public function getPrice(): CartPrice
    {
        return $this->calculatedCart->getPrice();
    }

    public function getLineItems(): ViewLineItemCollection
    {
        return $this->lineItems;
    }

    public function getCalculatedCart(): CalculatedCart
    {
        return $this->calculatedCart;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->calculatedCart->getErrors();
    }

    public function getDeliveries(): DeliveryCollection
    {
        return $this->calculatedCart->getDeliveries();
    }
}
