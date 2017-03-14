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

namespace Shopware\Bundle\CartBundle\Infrastructure\Delivery;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryMethod;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class DeliveryMethodService
{
    /**
     * @var DeliveryMethodGateway
     */
    private $gateway;

    /**
     * @param DeliveryMethodGateway $gateway
     */
    public function __construct(DeliveryMethodGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param CalculatedCart       $cart
     * @param ShopContextInterface $context
     *
     * @return DeliveryMethod[]
     */
    public function getAvailable(CalculatedCart $cart, ShopContextInterface $context): array
    {
        $deliveries = $this->gateway->getAll($context->getTranslationContext());

        $deliveries = array_filter(
            $deliveries,
            function (DeliveryMethod $method) {
                return $method->getType() === 0;
            }
        );

        return $deliveries;
    }
}
