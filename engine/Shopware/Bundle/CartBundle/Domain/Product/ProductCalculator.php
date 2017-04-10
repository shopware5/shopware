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

use Shopware\Bundle\CartBundle\Domain\Error\ProductDeliveryInformationNotFoundError;
use Shopware\Bundle\CartBundle\Domain\Error\ProductPriceNotFoundError;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedProductCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductCalculator
{
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

    public function __construct(
        ProductPriceGatewayInterface $priceGateway,
        PriceCalculator $priceCalculator,
        ProductDeliveryGatewayInterface $deliveryGateway
    ) {
        $this->priceGateway = $priceGateway;
        $this->priceCalculator = $priceCalculator;
        $this->deliveryGateway = $deliveryGateway;
    }

    public function calculate(
        LineItemCollection $collection,
        ShopContextInterface $context
    ): CalculatedProductCollection {
        $priceDefinitions = $this->priceGateway->get($collection, $context);

        $deliveryInformation = $this->deliveryGateway->get($collection, $context);

        $products = new CalculatedProductCollection();

        /** @var LineItemInterface $lineItem */
        foreach ($collection as $lineItem) {
            if (!$priceDefinitions->has($lineItem->getIdentifier())) {
                $products->addError(
                    new ProductPriceNotFoundError($lineItem->getIdentifier())
                );

                continue;
            }

            if (!$deliveryInformation->has($lineItem->getIdentifier())) {
                $products->addError(
                    new ProductDeliveryInformationNotFoundError($lineItem->getIdentifier())
                );

                continue;
            }

            $price = $this->priceCalculator->calculate(
                $priceDefinitions->get($lineItem->getIdentifier()),
                $context
            );

            $products->add(
                new CalculatedProduct(
                    $lineItem->getIdentifier(),
                    $lineItem->getQuantity(),
                    $lineItem,
                    $price,
                    $deliveryInformation->get($lineItem->getIdentifier())
                )
            );
        }

        return $products;
    }
}
