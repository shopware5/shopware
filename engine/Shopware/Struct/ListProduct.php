<?php

namespace Shopware\Struct;
use Shopware\Struct\Product\Manufacturer;
use Shopware\Struct\Product\Price;
use Shopware\Struct\Product\PriceGroup;
use Shopware\Struct\Product\PriceRule;
use Shopware\Struct\Product\Unit;

/**
 * @package Shopware\Struct
 */
class ListProduct extends Extendable
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
     * Contains a flag if the product has properties.
     * @var boolean
     */
    private $hasProperties = false;

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
    private $keywords;

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
     * Physical length of the product.
     * Used for area calculation.
     *
     * @var float
     */
    private $length;

    /**
     * Physical width of the product.
     * Used for area calculation.
     * @var float
     */
    private $weight;

    /**
     * Ean code of the product.
     * @var string
     */
    private $ean;

    /**
     * Flag if the product should be displayed
     * with a teaser flag within listings.
     *
     * @var float
     */
    private $highlight;

    /**
     * Contains the absolute cheapest price of each product variation.
     * This price is calculated over the shopware price service
     * getCheapestPrice function.
     *
     * @var Price
     */
    private $cheapestPrice;

    /**
     * Contains the price rule for the cheapest price.
     *
     * @var PriceRule
     */
    private $cheapestPriceRule;

    /**
     * @var PriceRule[]
     */
    private $priceRules = array();

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
     * @var PriceGroup
     */
    private $priceGroup;

    /**
     * Contains an offset of product states.
     * States defines which processed the product has already passed through,
     * like the price calculation, translation or other states.
     *
     * @var array
     */
    private $states = array();


    /**
     * Adds a new product state.
     *
     * @param $state
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

    /**
     * @return boolean
     */
    public function hasProperties()
    {
        return $this->hasProperties;
    }

    /**
     * @return boolean
     */
    public function isShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @return boolean
     */
    public function allowsNotification()
    {
        return $this->allowsNotification;
    }

    /**
     * @return float
     */
    public function highlight()
    {
        return $this->highlight;
    }

    /**
     * @param float $highlight
     */
    public function setHighlight($highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * @param boolean $allowsNotification
     */
    public function setAllowsNotification($allowsNotification)
    {
        $this->allowsNotification = $allowsNotification;
    }

    /**
     * @param boolean $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
    }


    /**
     * @param Unit $unit
     */
    public function setUnit(Unit $unit)
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
     * @param \Shopware\Struct\Tax $tax
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
     * @param \Shopware\Struct\Product\Price[] $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @return \Shopware\Struct\Product\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param \Shopware\Struct\Product\Manufacturer $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return \Shopware\Struct\Product\Manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param \Shopware\Struct\Media $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * @return \Shopware\Struct\Media
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param \Shopware\Struct\Product\Price $cheapestPrice
     */
    public function setCheapestPrice($cheapestPrice)
    {
        $this->cheapestPrice = $cheapestPrice;
    }

    /**
     * @return \Shopware\Struct\Product\Price
     */
    public function getCheapestPrice()
    {
        return $this->cheapestPrice;
    }

    /**
     * @param int $id
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
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $name
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
     * @param string $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param boolean $closeouts
     */
    public function setCloseouts($closeouts)
    {
        $this->closeouts = $closeouts;
    }

    /**
     * @return boolean
     */
    public function isCloseouts()
    {
        return $this->closeouts;
    }

    /**
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param float $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param array $keywords
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param float $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param string $longDescription
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;
    }

    /**
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }


    /**
     * @param int $minStock
     */
    public function setMinStock($minStock)
    {
        $this->minStock = $minStock;
    }

    /**
     * @return int
     */
    public function getMinStock()
    {
        return $this->minStock;
    }

    /**
     * @param \DateTime $releaseDate
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * @return \DateTime
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * @param int $shippingTime
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
    }

    /**
     * @return int
     */
    public function getShippingTime()
    {
        return $this->shippingTime;
    }

    /**
     * @param string $shortDescription
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
     * @param int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $variantId
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;
    }

    /**
     * @return int
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param boolean $hasProperties
     */
    public function setHasProperties($hasProperties)
    {
        $this->hasProperties = $hasProperties;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return PriceGroup
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param \Shopware\Struct\Product\PriceGroup $priceGroup
     */
    public function setPriceGroup(PriceGroup $priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    /**
     * @return \Shopware\Struct\Product\PriceRule[]
     */
    public function getPriceRules()
    {
        return $this->priceRules;
    }

    /**
     * @param \Shopware\Struct\Product\PriceRule[] $priceRules
     */
    public function setPriceRules($priceRules)
    {
        $this->priceRules = $priceRules;
    }

    /**
     * @return \Shopware\Struct\Product\PriceRule
     */
    public function getCheapestPriceRule()
    {
        return $this->cheapestPriceRule;
    }

    /**
     * @param \Shopware\Struct\Product\PriceRule $cheapestPriceRule
     */
    public function setCheapestPriceRule($cheapestPriceRule)
    {
        $this->cheapestPriceRule = $cheapestPriceRule;
    }
}