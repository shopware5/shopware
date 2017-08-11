<?php

namespace Shopware\Category\Struct;

use Shopware\Framework\Struct\Collection;

class CategoryIdentityCollection extends Collection
{
    /**
     * @var CategoryIdentity[]
     */
    protected $elements = [];

    public function add(CategoryIdentity $category): void
    {
        $key = $this->getKey($category);
        $this->elements[$key] = $category;
    }

    public function remove(int $id): void
    {
        parent::doRemoveByKey($id);
    }

    public function removeElement(CategoryIdentity $category): void
    {
        parent::doRemoveByKey($this->getKey($category));
    }

    public function exists(CategoryIdentity $category): bool
    {
        return parent::has($this->getKey($category));
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
        return $this->map(function (CategoryIdentity $category) {
            return $category->getId();
        });
    }

    public function getPaths(): array
    {
        return $this->map(function (CategoryIdentity $category) {
            return $category->getPath();
        });
    }

    public function getIdsIncludingPaths(): array
    {
        $ids = [];
        foreach ($this->elements as $category) {
            $ids[] = $category->getId();
            foreach ($category->getPath() as $id) {
                $ids[] = $id;
            }
        }

        return array_keys(array_flip($ids));
    }

    /**
     * @param int|null $parentId
     *
     * @return CategoryIdentity[]
     */
    public function getTree(?int $parentId): array
    {
        $result = [];
        foreach ($this->elements as $category) {
            if ($category->getParent() != $parentId) {
                continue;
            }
            $category->setChildren(
                $this->getTree($category->getId())
            );
            $result[] = $category;
        }

        return $result;
    }

    protected function getKey(CategoryIdentity $element): int
    {
        return $element->getId();
    }

    public function sortByPosition(): CategoryIdentityCollection
    {
        $this->sort(function(CategoryIdentity $a, CategoryIdentity $b) {
            return $a->getPosition() <=> $b->getPosition();
        });
        return $this;
    }
}