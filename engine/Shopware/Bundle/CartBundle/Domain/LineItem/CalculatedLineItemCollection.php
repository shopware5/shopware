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

namespace Shopware\Bundle\CartBundle\Domain\LineItem;

use Shopware\Bundle\CartBundle\Domain\Collection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;

class CalculatedLineItemCollection extends Collection
{
    /**
     * @var CalculatedLineItemInterface[]
     */
    protected $items = [];

    /**
     * @param CalculatedLineItemInterface $item
     */
    public function add($item)
    {
        $this->items[$item->getIdentifier()] = $item;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return parent::has($identifier);
    }

    /**
     * @param string $identifier
     *
     * @return null|CalculatedLineItemInterface
     */
    public function get($identifier)
    {
        return parent::get($identifier);
    }

    /**
     * @param string $identifier
     */
    public function remove($identifier)
    {
        return parent::remove($identifier);
    }

    /**
     * @return string[]
     */
    public function getIdentifiers()
    {
        return array_keys($this->items);
    }

    /**
     * @return PriceCollection
     */
    public function getPrices()
    {
        return new PriceCollection(
            $this->map(
                function (CalculatedLineItemInterface $item) {
                    return $item->getPrice();
                }
            )
        );
    }

    /**
     * @param string $class
     *
     * @return CalculatedLineItemCollection
     */
    public function filterClass($class)
    {
        return new self($this->getItemsOfClass($class));
    }

    /**
     * @return CalculatedLineItemCollection
     */
    public function filterGoods()
    {
        return new self($this->getItemsOfClass(Goods::class));
    }

    /**
     * @param string $class
     *
     * @return CalculatedLineItemInterface[]
     */
    private function getItemsOfClass($class)
    {
        return array_filter(
            $this->items,
            function (CalculatedLineItemInterface $lineItem) use ($class) {
                return $lineItem instanceof $class;
            }
        );
    }
}
