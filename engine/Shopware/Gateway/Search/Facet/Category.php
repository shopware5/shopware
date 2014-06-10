<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Category implements Facet
{
    /**
     * Flag if the facet is filtered with a condition
     * @var bool
     */
    private $filtered = false;

    /**
     * @var \Shopware\Struct\Category[]
     */
    private $categories;

    /**
     * @return string
     */
    public function getName()
    {
        return 'category';
    }

    /**
     * @param bool $filtered
     */
    public function setFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->filtered;
    }

    /**
     * @return \Shopware\Struct\Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \Shopware\Struct\Category[] $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

}