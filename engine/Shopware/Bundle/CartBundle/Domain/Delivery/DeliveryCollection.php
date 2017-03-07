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
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;

class DeliveryCollection extends Collection
{
    /**
     * @var Delivery[]
     */
    protected $items = [];

    public function add(Delivery $delivery): void
    {
        $this->items[] = $delivery;
    }

    public function remove($key): ? Delivery
    {
        return parent::remove($key);
    }

    public function offsetGet($offset): ? Delivery
    {
        return parent::offsetGet($offset);
    }

    public function set($key, Delivery $value): void
    {
        parent::set($key, $value);
    }

    public function get($key): ? Delivery
    {
        return parent::get($key);
    }

    /**
     * @return Delivery[]
     */
    public function getValues(): array
    {
        return parent::getValues();
    }

    /**
     * Sorts the delivery collection by earliest delivery date
     */
    public function sort(): void
    {
        usort(
            $this->items,
            function (Delivery $a, Delivery $b) {
                if ($a->getAddress() != $b->getAddress()) {
                    return -1;
                }

                return $a->getDeliveryDate()->getEarliest() > $b->getDeliveryDate()->getEarliest();
            }
        );
    }

    public function getDelivery(DeliveryDate $deliveryDate, Address $address): ? Delivery
    {
        foreach ($this->items as $delivery) {
            if ($delivery->getDeliveryDate() != $deliveryDate) {
                continue;
            }
            if ($delivery->getAddress() != $address) {
                continue;
            }

            return $delivery;
        }

        return null;
    }

    /**
     * @param Deliverable|CalculatedLineItemInterface $item
     *
     * @return bool
     */
    public function contains(Deliverable $item)
    {
        foreach ($this->items as $delivery) {
            if ($delivery->getPositions()->has($item->getIdentifier())) {
                return true;
            }
        }

        return false;
    }
}
