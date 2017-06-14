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

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinitionCollection;
use Shopware\Bundle\CartBundle\Domain\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Rule\Validatable;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

class ProductData extends Struct implements Validatable
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var \Shopware\Bundle\CartBundle\Domain\Rule\Rule
     */
    protected $rule;

    /**
     * @var PriceDefinitionCollection
     */
    protected $prices;

    /**
     * @var DeliveryInformation
     */
    protected $deliveryInformation;

    /**
     * @param string                    $number
     * @param PriceDefinitionCollection $prices
     * @param DeliveryInformation       $deliveryInformation
     * @param Rule                      $rule
     */
    public function __construct(
        string $number,
        PriceDefinitionCollection $prices,
        DeliveryInformation $deliveryInformation,
        Rule $rule
    ) {
        $this->number = $number;
        $this->prices = $prices;
        $this->deliveryInformation = $deliveryInformation;
        $this->rule = $rule;
    }

    public function getDeliveryInformation(): DeliveryInformation
    {
        return $this->deliveryInformation;
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return PriceDefinitionCollection
     */
    public function getPrices(): PriceDefinitionCollection
    {
        return $this->prices;
    }

    public function getPrice(int $quantity): ? PriceDefinition
    {
        $prices = $this->prices->getIterator()->getArrayCopy();
        usort(
            $prices,
            function (PriceDefinition $a, PriceDefinition $b) {
                return $a->getQuantity() < $b->getQuantity();
            }
        );

        /** @var PriceDefinition $price */
        foreach ($prices as $price) {
            if ($price->getQuantity() <= $quantity) {
                return $price;
            }
        }

        return null;
    }
}
