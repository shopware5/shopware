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

namespace Shopware\Category\Gateway;

use Shopware\Category\Struct\CategoryIdentity;
use Shopware\Framework\Struct\Collection;
use Shopware\Search\SearchResultInterface;
use Shopware\Search\SearchResultTrait;

class CategorySearchResult extends Collection implements SearchResultInterface
{
    use SearchResultTrait;

    /**
     * @var CategoryIdentity[]
     */
    protected $elements = [];

    public function __construct(array $elements, int $total)
    {
        parent::__construct($elements);
        $this->total = $total;
    }

    public function add(CategoryIdentity $identity): void
    {
        $this->elements[$this->getKey($identity)] = $identity;
    }

    public function remove(string $number): void
    {
        parent::doRemoveByKey($number);
    }

    public function removeElement(CategoryIdentity $identity): void
    {
        parent::doRemoveByKey($this->getKey($identity));
    }

    public function get(int $id): ? CategoryIdentity
    {
        if ($this->has($id)) {
            return $this->elements[$id];
        }

        return null;
    }

    public function getIds(): array
    {
        return $this->map(function (CategoryIdentity $identity) {
            return $identity->getId();
        });
    }

    public function getIdsIncludingPaths(): array
    {
        $ids = [];
        foreach ($this->elements as $identity) {
            $ids[] = $identity->getId();
            foreach ($identity->getPath() as $id) {
                $ids[] = $id;
            }
        }

        return array_keys(array_flip($ids));
    }

    public function getPaths(): array
    {
        return $this->map(function (CategoryIdentity $identity) {
            return $identity->getPath();
        });
    }

    protected function getKey(CategoryIdentity $element): string
    {
        return $element->getId();
    }
}
