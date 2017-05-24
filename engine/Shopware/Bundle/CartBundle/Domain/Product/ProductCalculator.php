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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedProductCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\StoreFrontBundle\Common\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ProductCalculator
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    public function calculate(
        LineItemCollection $collection,
        ShopContextInterface $context,
        StructCollection $dataCollection
    ): CalculatedProductCollection {
        $products = new CalculatedProductCollection();

        /** @var LineItemInterface $lineItem */
        foreach ($collection as $lineItem) {
            if (!$dataCollection->has($lineItem->getIdentifier())) {
                continue;
            }

            /** @var ProductData $definition */
            $definition = $dataCollection->get($lineItem->getIdentifier());

            $priceDefinition = $lineItem->getPriceDefinition();
            if (!$priceDefinition) {
                $priceDefinition = $definition->getPrice($lineItem->getQuantity());
            }

            $priceDefinition = new PriceDefinition(
                $priceDefinition->getPrice(),
                $priceDefinition->getTaxRules(),
                $lineItem->getQuantity(),
                $priceDefinition->isCalculated()
            );

            $deliveryInformation = $definition->getDeliveryInformation();

            $price = $this->priceCalculator->calculate($priceDefinition, $context);

            $products->add(
                new CalculatedProduct(
                    $lineItem->getIdentifier(),
                    $lineItem->getQuantity(),
                    $lineItem,
                    $price,
                    $deliveryInformation,
                    $definition->getRule()
                )
            );
        }

        return $products;
    }
}
