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

use Shopware\Bundle\CartBundle\Domain\KeyCollection;

class LineItemCollection extends KeyCollection
{
    /**
     * @var LineItemInterface[]
     */
    protected $elements = [];

    public function add(LineItemInterface $lineItem): void
    {
        parent::doAdd($lineItem);
    }

    public function remove(string $identifier): void
    {
        parent::doRemoveByKey($identifier);
    }

    public function removeElement(LineItemInterface $lineItem): void
    {
        parent::doRemoveByKey($this->getKey($lineItem));
    }

    public function exists(LineItemInterface $lineItem): bool
    {
        return parent::has($this->getKey($lineItem));
    }

    public function get(string $identifier): ? LineItemInterface
    {
        if ($this->has($identifier)) {
            return $this->elements[$identifier];
        }

        return null;
    }

    public function filterType(string $type): LineItemCollection
    {
        return $this->filter(
            function (LineItemInterface $lineItem) use ($type) {
                return $lineItem->getType() === $type;
            }
        );
    }

    public function getIdentifiers(): array
    {
        return $this->getKeys();
    }

    /**
     * @param LineItemInterface $element
     *
     * @return string
     */
    protected function getKey($element): string
    {
        return $element->getIdentifier();
    }
}
