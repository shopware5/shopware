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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

class CategoryCollection extends Collection implements \JsonSerializable
{
    /**
     * @var Category[]
     */
    protected $elements;

    /**
     * @param Category[] $elements
     */
    public function __construct(array $elements)
    {
        parent::__construct($elements);
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return array_map(function (Category $category) {
            return $category->getId();
        }, $this->elements);
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return array_map(function (Category $category) {
            return $category->getPath();
        }, $this->elements);
    }

    /**
     * @return int[]
     */
    public function getIdsIncludingPaths(): array
    {
        $ids = [];
        foreach ($this->elements as $category) {
            $ids = array_merge($ids, [$category->getId()], $category->getPath());
        }

        return array_keys(array_flip($ids));
    }

    /**
     * @param int|null $parentId
     *
     * @return Category[]
     */
    public function getTree(?int $parentId): array
    {
        $result = [];
        foreach ($this->elements as $category) {
            if ($category->getParentId() != $parentId) {
                continue;
            }
            $category->setChildren(
                $this->getTree((int) $category->getId())
            );
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param string|int $key
     *
     * @return null|Category
     */
    public function get($key): ? Category
    {
        return parent::get($key);
    }

    /**
     * @param int $id
     *
     * @return null|Category
     */
    public function getById(int $id): ? Category
    {
        foreach ($this->elements as $category) {
            if ($category->getId() === $id) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @param $key
     * @param Category $category
     */
    public function set($key, Category $category): void
    {
        $this->elements[$key] = $category;
    }

    /**
     * @param Category $element
     */
    public function add(Category $element): void
    {
        parent::add($element);
    }
}
