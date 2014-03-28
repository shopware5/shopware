<?php

namespace Shopware\Struct;

/**
 * Merge von s_articles und s_articles_details
 *
 * Ein "Struct\Product" stellt immer eine Variante im Frontend da.
 * Im Frontend wird eh immer nur mit einer Variante gearbeitet.
 * === Einzige Ausnahme soweit bekannt ist der Tabellen Konfigurator (der soll vllt raus fliegen)
 *
 * @package Shopware\Struct
 */
class ProductMini
{
    /**
     * State for a calculated product price
     */
    const STATE_PRICE_CALCULATED = 'price_calculated';

    /**
     * State for a translated product.
     */
    const STATE_TRANSLATED = 'translated';

    /**
     * Unique identifier
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * Stock of the product
     * @var int
     */
    private $inStock;

    /**
     * @var ProductMini
     */
    private $mainProduct;

    /**
     * Price of the current variant.
     * @var Price[]
     */
    private $prices;

    /**
     * Contains an array of attribute structs.
     *
     * @var Attribute[]
     */
    private $attributes;

    /**
     * Contains an offset of product states.
     * States defines which processed the product has already passed through,
     * like the price calculation, translation or other states.
     *
     * @var array
     */
    private $states = array();

    /**
     * Adds a new attribute struct to the product.
     *
     * @param $name
     * @param Attribute $attribute
     */
    public function addAttribute($name, Attribute $attribute)
    {
        $this->attributes[$name] = $attribute;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $inStock
     * @return $this
     */
    public function setInStock($inStock)
    {
        $this->inStock = $inStock;
        return $this;
    }

    /**
     * @return int
     */
    public function getInStock()
    {
        return $this->inStock;
    }

    /**
     * @param \Shopware\Struct\ProductMini $mainProduct
     * @return $this
     */
    public function setMainProduct($mainProduct)
    {
        $this->mainProduct = $mainProduct;
        return $this;
    }

    /**
     * @return \Shopware\Struct\ProductMini
     */
    public function getMainProduct()
    {
        return $this->mainProduct;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Shopware\Struct\Price[] $prices
     * @return $this
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }


    /**
     * @return \Shopware\Struct\Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @return Attribute
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * Adds a new product state.
     *
     * @param $state
     * @return $this
     */
    public function addState($state)
    {
        $this->states[] = $state;
        return $this;
    }

    /**
     * Checks if the product has a specify state
     * @param $state
     * @return bool
     */
    public function hasState($state)
    {
        return in_array($state, $this->states);
    }
}