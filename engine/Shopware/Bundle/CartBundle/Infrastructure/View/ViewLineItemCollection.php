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

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Collection;

class ViewLineItemCollection extends Collection
{
    /**
     * @var ViewLineItemInterface[]
     */
    protected $items = [];

    /**
     * @param ViewLineItemInterface $item
     */
    public function add($item)
    {
        $this->items[$item->getLineItem()->getIdentifier()] = $item;
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
     * @return null|ViewLineItemInterface
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
}
