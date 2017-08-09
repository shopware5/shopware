<?php

namespace Shopware\Storefront\Navigation;

use Shopware\Category\Struct\Category;

class Navigation
{
    /**
     * @var Category[]
     */
    protected $tree;

    /**
     * @var Category
     */
    protected $activeCategory;

    public function __construct(Category $activeCategory, array $tree)
    {
        $this->tree = $tree;
        $this->activeCategory = $activeCategory;
    }

    public function getTree(): array
    {
        return $this->tree;
    }

    public function getActiveCategory(): Category
    {
        return $this->activeCategory;
    }

    public function setTree(array $tree): void
    {
        $this->tree = $tree;
    }

    public function setActiveCategory(Category $activeCategory): void
    {
        $this->activeCategory = $activeCategory;
    }
}