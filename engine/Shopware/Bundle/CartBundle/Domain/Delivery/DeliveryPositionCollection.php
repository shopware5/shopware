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

namespace Shopware\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\Collection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;

class DeliveryPositionCollection extends Collection
{
    public function add(DeliveryPosition $position): void
    {
        $this->elements[] = $position;
    }

    public function remove($key): ? DeliveryPosition
    {
        return parent::remove($key);
    }

    public function offsetGet($offset): ? DeliveryPosition
    {
        return parent::offsetGet($offset);
    }

    public function set($key, DeliveryPosition $value): void
    {
        parent::set($key, $value);
    }

    public function get($key): ? DeliveryPosition
    {
        return parent::get($key);
    }

    /**
     * @return DeliveryPosition[]
     */
    public function getValues(): array
    {
        return parent::getValues();
    }

    public function getPrices(): PriceCollection
    {
        return new PriceCollection(
            array_map(
                function (DeliveryPosition $position) {
                    return $position->getPrice();
                },
                $this->elements
            )
        );
    }

    public function getLineItems(): CalculatedLineItemCollection
    {
        return new CalculatedLineItemCollection(
            array_map(
                function (DeliveryPosition $position) {
                    return $position->getItem();
                },
                $this->elements
            )
        );
    }
}
