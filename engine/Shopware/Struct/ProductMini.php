<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class ProductMini extends Base implements Extendable
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
     * Unique identifier of the product (s_articles).
     *
     * @var int
     */
    private $id;

    /**
     * Unique identifier of the product variation (s_articles_details).
     *
     * @var int
     */
    private $variantId;

    /**
     * Contains the product name.
     *
     * @var string
     */
    private $name;

    /**
     * Unique identifier field.
     * Shopware order number for the product, which
     * is used to load the product or add the product
     * to the basket.
     *
     * @var string
     */
    private $number;

    /**
     * Stock value of the product.
     * Displays how many unit are left in the stock.
     *
     * @var int
     */
    private $stock;

    /**
     * Short description of the product.
     * Describes the product in one or two sentences.
     *
     * @var string
     */
    private $shortDescription;

    /**
     * A long description of the product.
     *
     * @var string
     */
    private $longDescription;

    /**
     * Defines the date when the product was released / will be
     * released and can be ordered.
     *
     * @var \DateTime
     */
    private $releaseDate;

    /**
     * Defines the required time in days to deliver the product.
     *
     * @var int
     */
    private $shippingTime;

    /**
     * Defines if the product has no shipping costs.
     *
     * @var boolean
     */
    private $shippingFree;

    /**
     * Defines that the product are no longer
     * available if the last item is sold.
     *
     * @var boolean
     */
    private $closeouts;

    /**
     * Defines the date which the product was created in the
     * database.
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Defines a list of keywords for this product.
     *
     * @var array
     */
    private $keyWords;

    /**
     * Defines if the customer can be set an email
     * notification for this product if it is sold out.
     *
     * @var boolean
     */
    private $allowsNotification;

    /**
     * Additional information text for the product variation.
     *
     * @var string
     */
    private $additional;

    /**
     * Minimal stock value for the product.
     * @var int
     */
    private $minStock;

    /**
     * Physical height of the product.
     * Used for area calculation.
     * @var float
     */
    private $height;

    /**
     * Physical width of the product.
     * Used for area calculation.
     * @var float
     */
    private $width;

    /**
     * Physical len of the product.
     * Used for area calculation.
     *
     * @var float
     */
    private $len;

    /**
     * Physical width of the product.
     * Used for area calculation.
     * @var float
     */
    private $weight;

    private $ean;

    private $minPurchase;

    private $maxPurchase;

    private $purchaseSteps;

    private $isHighlight;

    private $voteAverage;

    /**
     * Contains the cheapest price of this product variation.
     * This price is calculated over the shopware price service
     * getCheapestVariantPrice function.
     *
     * @var Price
     */
    private $cheapestVariantPrice;

    /**
     * Contains the absolute cheapest price of each product variation.
     * This price is calculated over the shopware price service
     * getCheapestProductPrice function.
     *
     * @var Price
     */
    private $cheapestProductPrice;

    /**
     * Price of the current variant.
     * @var Price[]
     */
    private $prices = array();

    /**
     * @var Unit
     */
    private $unit;

    /**
     * @var Tax
     */
    private $tax;

    /**
     * @var Manufacturer
     */
    private $manufacturer;

    /**
     * Contains the product cover which displayed
     * as product image in listings or sliders.
     *
     * @var Media
     */
    private $cover;

    /**
     * Contains an array of attribute structs.
     *
     * @var Attribute[]
     */
    private $attributes = array();

    /**
     * Contains an offset of product states.
     * States defines which processed the product has already passed through,
     * like the price calculation, translation or other states.
     *
     * @var array
     */
    private $states = array();

    /**
     * @param int $id
     *
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param int $variantId
     *
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;

    }

    /**
     * @param string $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;

    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     *
     */
    public function setNumber($number)
    {
        $this->number = $number;

    }

    /**
     * @return mixed
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param mixed $stock
     *
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

    }

    /**
     * @return bool
     */
    public function hasStock()
    {
        return (bool)($this->stock > 0);
    }

    /**
     * @param string $shortDescription
     *
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $longDescription
     *
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

    }

    /**
     * @return mixed
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * @return mixed
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param mixed $releaseDate
     *
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;

    }

    /**
     * @return mixed
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param mixed $shippingTime
     *
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;

    }

    /**
     * @return mixed
     */
    public function isShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param mixed $shippingFree
     *
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;

    }

    /**
     * @return boolean
     */
    public function isCloseouts()
    {
        return $this->closeouts;
    }

    /**
     * @param boolean $closeouts
     *
     */
    public function setCloseouts($closeouts)
    {
        $this->closeouts = $closeouts;

    }

    /**
     * @param \Shopware\Struct\Price[] $prices
     *
     */
    public function setPrices(array $prices)
    {
        $this->prices = $prices;

    }

    /**
     * @return \Shopware\Struct\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param \Shopware\Struct\Manufacturer $manufacturer
     *
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

    }

    /**
     * @return \Shopware\Struct\Manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param \Shopware\Struct\Tax $tax
     *
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

    }

    /**
     * @return \Shopware\Struct\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param \Shopware\Struct\Unit $unit
     *
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

    }

    /**
     * @return Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return \Shopware\Struct\Media
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param \Shopware\Struct\Media $cover
     * @return $this
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
        return $this;
    }

    /**
     * @param \Shopware\Struct\Price $cheapestVariantPrice
     * @return $this
     */
    public function setCheapestVariantPrice($cheapestVariantPrice)
    {
        $this->cheapestVariantPrice = $cheapestVariantPrice;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Price
     */
    public function getCheapestVariantPrice()
    {
        return $this->cheapestVariantPrice;
    }

    /**
     * @param \Shopware\Struct\Price $cheapestProductPrice
     * @return $this
     */
    public function setCheapestProductPrice($cheapestProductPrice)
    {
        $this->cheapestProductPrice = $cheapestProductPrice;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Price
     */
    public function getCheapestProductPrice()
    {
        return $this->cheapestProductPrice;
    }

    /**
     * Adds a new attribute struct into the class storage.
     * The passed name is used as unique identifier and has to be stored too.
     *
     * @param string $name
     * @param Attribute $attribute
     */
    public function addAttribute($name, Attribute $attribute)
    {
        $this->attributes[$name] = $attribute;
    }

    /**
     * Returns a single attribute struct element of this class.
     * The passed name is used as unique identifier.
     *
     * @param $name
     * @return Attribute
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * Helper function which checks if an associated
     * attribute exists.
     *
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns all stored attribute structures of this class.
     * The array has to be an associated array with name and attribute instance.
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Adds a new product state.
     *
     * @param $state
     *
     */
    public function addState($state)
    {
        $this->states[] = $state;

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