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

class LineItemCollection extends Collection
{
    /**
     * @var LineItemInterface[]
     */
    protected $items;

    /**
     * @param LineItemInterface $item
     */
    public function add($item)
    {
        $this->items[$item->getIdentifier()] = $item;
    }

    /**
     * @param string $identifier
     *
     * @return LineItemInterface
     */
    public function get($identifier)
    {
        return parent::get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return parent::has($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        parent::remove($identifier);
    }

    /**
     * @param string $type
     *
     * @return LineItemCollection
     */
    public function filterType($type)
    {
        return new self($this->getLineItemsOfType($type));
    }

    /**
     * @return string[]
     */
    public function getIdentifiers()
    {
        return $this->keys();
    }

    /**
     * @param string $type
     *
     * @return LineItemInterface[]
     */
    private function getLineItemsOfType($type)
    {
        return array_filter(
            $this->items,
            function (LineItemInterface $lineItem) use ($type) {
                return $lineItem->getType() === $type;
            }
        );
    }
}
