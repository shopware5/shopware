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

namespace Shopware\Shop\Struct;

use Shopware\Framework\Struct\Collection;

class ShopCollection extends Collection
{
    /**
     * @var Shop[]
     */
    protected $elements = [];

    public function add(Shop $shop): void
    {
        $key = $this->getKey($shop);
        $this->elements[$key] = $shop;
    }

    public function remove(int $id): void
    {
        parent::doRemoveByKey($id);
    }

    public function removeElement(Shop $shop): void
    {
        parent::doRemoveByKey($this->getKey($shop));
    }

    public function exists(Shop $shop): bool
    {
        return parent::has($this->getKey($shop));
    }

    public function get(int $id): ? Shop
    {
        if ($this->has($id)) {
            return $this->elements[$id];
        }

        return null;
    }

    protected function getKey(Shop $element): int
    {
        return $element->getId();
    }

    public function sortByPosition(): ShopCollection
    {
        $this->sort(function(Shop $a, Shop $b) {
            return $a->getPosition() <=> $b->getPosition();
        });
        return $this;
    }
}
