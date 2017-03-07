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

namespace Shopware\Bundle\CartBundle\Domain\LineItem;

use Shopware\Bundle\CartBundle\Domain\Collection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;

class CalculatedLineItemCollection extends Collection
{
    public function add(CalculatedLineItemInterface $lineItem): void
    {
        $this->elements[] = $lineItem;
    }

    public function remove($key): ? CalculatedLineItemInterface
    {
        return parent::remove($key);
    }

    public function offsetGet($offset): ? CalculatedLineItemInterface
    {
        return parent::offsetGet($offset);
    }

    public function set($key, CalculatedLineItemInterface $value): void
    {
        parent::set($key, $value);
    }

    public function get($key): ? CalculatedLineItemInterface
    {
        return parent::get($key);
    }

    /**
     * @return CalculatedLineItemInterface[]
     */
    public function getValues(): array
    {
        return parent::getValues();
    }

    public function getIdentifiers(): array
    {
        return array_map(
            function (CalculatedLineItemInterface $lineItem) {
                return $lineItem->getIdentifier();
            },
            $this->elements
        );
    }

    public function getPrices(): PriceCollection
    {
        return new PriceCollection(
            array_map(
                function (CalculatedLineItemInterface $item) {
                    return $item->getPrice();
                },
                $this->elements
            )
        );
    }

    public function filterClass(string $class): CalculatedLineItemCollection
    {
        return $this->filter(
            function (CalculatedLineItemInterface $lineItem) use ($class) {
                return $lineItem instanceof $class;
            }
        );
    }

    public function filterGoods(): CalculatedLineItemCollection
    {
        $class = Goods::class;

        return $this->filter(
            function (CalculatedLineItemInterface $lineItem) use ($class) {
                return $lineItem instanceof $class;
            }
        );
    }
}
