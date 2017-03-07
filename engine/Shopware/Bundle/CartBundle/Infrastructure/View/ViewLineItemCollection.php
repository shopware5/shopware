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

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Collection;

class ViewLineItemCollection extends Collection
{
    /**
     * @var ViewLineItemInterface[]
     */
    protected $elements;

    public function add(ViewLineItemInterface $lineItem): void
    {
        $this->elements[] = $lineItem;
    }

    public function remove($key): ? ViewLineItemInterface
    {
        return parent::remove($key);
    }

    public function offsetGet($offset): ? ViewLineItemInterface
    {
        return parent::offsetGet($offset);
    }

    public function set($key, ViewLineItemInterface $value): void
    {
        parent::set($key, $value);
    }

    public function get($key): ? ViewLineItemInterface
    {
        return parent::get($key);
    }

    /**
     * @return ViewLineItemInterface[]
     */
    public function getValues(): array
    {
        return parent::getValues();
    }

    public function getIdentifiers(): array
    {
        return $this->map(
            function (ViewLineItemInterface $lineItem) {
                return $lineItem->getLineItem()->getIdentifier();
            }
        );
    }
}
