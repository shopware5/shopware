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
use Shopware\Bundle\CartBundle\Domain\Error\ErrorCollection;
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
     * @var CartContainer
     */
    protected $cartContainer;

    /**
     * @var CalculatedLineItemCollection
     */
    protected $lineItems;

    /**
     * @var DeliveryCollection
     */
    protected $deliveries;

    /**
     * @var ErrorCollection
     */
    protected $errors;

    public function __construct(
        CartContainer $cartContainer,
        CalculatedLineItemCollection $lineItems,
        CartPrice $price,
        DeliveryCollection $deliveries,
        ErrorCollection $errors
    ) {
        $this->cartContainer = $cartContainer;
        $this->lineItems = $lineItems;
        $this->price = $price;
        $this->deliveries = $deliveries;
        $this->errors = $errors;
    }

    public function getName(): string
    {
        return $this->cartContainer->getName();
    }

    public function getToken(): string
    {
        return $this->cartContainer->getToken();
    }

    public function getPrice(): CartPrice
    {
        return clone $this->price;
    }

    public function getCartContainer(): CartContainer
    {
        return clone $this->cartContainer;
    }

    public function getLineItems(): CalculatedLineItemCollection
    {
        return clone $this->lineItems;
    }

    public function getDeliveries(): DeliveryCollection
    {
        return clone $this->deliveries;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }
}
