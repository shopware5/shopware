<?php

namespace Shopware\Gateway;

class Result
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var array
     */
    private $suppliers;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var array
     */
    private $prices;

    /**
     * @var array
     */
    private $categories;

    /**
     * @var int
     */
    private $totalCount;


    /**
     * Returns an array with product order numbers.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param array $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return array
     */
    public function getSuppliers()
    {
        return $this->suppliers;
    }

    /**
     * @param array $suppliers
     */
    public function setSuppliers($suppliers)
    {
        $this->suppliers = $suppliers;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

}