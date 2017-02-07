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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;

class ProductProcessor implements CartProcessorInterface
{
    const TYPE_PRODUCT = 'product';

    /**
     * @var ProductPriceGatewayInterface
     */
    private $priceGateway;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var ProductDeliveryGatewayInterface
     */
    private $deliveryGateway;

    /**
     * @param ProductPriceGatewayInterface $priceGateway
     * @param PriceCalculator $priceCalculator
     * @param ProductDeliveryGatewayInterface $deliveryGateway
     */
    public function __construct(
        ProductPriceGatewayInterface $priceGateway,
        PriceCalculator $priceCalculator,
        ProductDeliveryGatewayInterface $deliveryGateway
    ) {
        $this->priceGateway = $priceGateway;
        $this->priceCalculator = $priceCalculator;
        $this->deliveryGateway = $deliveryGateway;
    }

    /**
     * @param Cart $cart
     * @param ProcessorCart $processorCart
     * @param CartContextInterface $context
     * @throws \RuntimeException
     */
    public function process(
        Cart $cart,
        ProcessorCart $processorCart,
        CartContextInterface $context
    ) {
        $collection = $cart->getLineItems()->filterType(self::TYPE_PRODUCT);
        if (count($collection) === 0) {
            return;
        }

        $priceDefinitions = $this->priceGateway->get($collection, $context);

        $deliveryInformation = $this->deliveryGateway->get($collection, $context);

        /** @var LineItem $lineItem */
        foreach ($collection as $lineItem) {
            if (!array_key_exists($lineItem->getIdentifier(), $priceDefinitions)) {
                throw new \RuntimeException(sprintf(
                    'No price found for product %s',
                    $lineItem->getIdentifier()
                ));
            }

            if (!array_key_exists($lineItem->getIdentifier(), $deliveryInformation)) {
                throw new \RuntimeException(sprintf(
                    'No delivery information found for product %s',
                    $lineItem->getIdentifier()
                ));
            }

            $price = $this->priceCalculator->calculate(
                $priceDefinitions[$lineItem->getIdentifier()],
                $context
            );

            $product = new CalculatedProduct(
                $lineItem->getIdentifier(),
                $lineItem->getQuantity(),
                $lineItem,
                $price,
                $deliveryInformation[$lineItem->getIdentifier()]
            );

            $processorCart->getLineItems()->add($product);
        }
    }
}
