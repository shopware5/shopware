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
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

class ViewCart extends Struct
{
    /**
     * @var ViewLineItemCollection
     */
    protected $viewLineItems;

    /**
     * @var CalculatedCart
     */
    protected $calculatedCart;

    public function __construct(CalculatedCart $calculatedCart)
    {
        $this->calculatedCart = $calculatedCart;

        $this->viewLineItems = new ViewLineItemCollection(
            $calculatedCart->getCalculatedLineItems()->filterInstance(ViewLineItemInterface::class)->getIterator()->getArrayCopy()
        );
    }

    public function getPrice(): CartPrice
    {
        return $this->calculatedCart->getPrice();
    }

    public function getViewLineItems(): ViewLineItemCollection
    {
        return $this->viewLineItems;
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
