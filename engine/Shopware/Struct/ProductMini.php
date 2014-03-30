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
     * Unique identifier
     * @var int
     */
    private $id;

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
     * Alphanumeric description how the product
     * units are delivered.
     *
     * Example: bottle, box, pair
     *
     * @var string
     */
    private $packUnit;

    /**
     * Contains the numeric value of the purchase unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *  bottle price  7,- €
     *  unit price    10,- €
     *
     * @var float
     */
    private $purchaseUnit;

    /**
     * Contains the numeric value of the reference unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *  bottle price  7,- €
     *  unit price    10,- €
     *
     * @var float
     */
    private $referenceUnit;

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


//    private $createdAt;
//
//    private $keyWords;
//
//    private $allowsNotification;
//
//    private $availableFrom;
//
//    private $availableTo;
//
//    private $additional;
//
//    private $minStock;
//
//    private $height;
//
//    private $width;
//
//    private $len;
//
//    private $weight;
//
//    private $ean;
//
//    private $minPurchase;
//
//    private $maxPurchase;
//
//    private $purchaseSteps;
//
//    private $highlight;
//
//    private $voteAverage;




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
     * @var Unit
     */
    private $unit;

    /**
     * @var Media[]
     */
    private $media;

    /**
     * @var Tax
     */
    private $tax;

    /**
     * @var Manufacturer
     */
    private $manufacturer;

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
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
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
     * @return $this
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
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
     * @return $this
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;
        return $this;
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
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * @param mixed $packUnit
     * @return $this
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param mixed $purchaseUnit
     * @return $this
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * @param mixed $referenceUnit
     * @return $this
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;
        return $this;
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
     * @return $this
     */
    public function setReleaseDate($releaseDate)
    {
        $this->releaseDate = $releaseDate;
        return $this;
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
     * @return $this
     */
    public function setShippingTime($shippingTime)
    {
        $this->shippingTime = $shippingTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param mixed $shippingFree
     * @return $this
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
        return $this;
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
     * @param \Shopware\Struct\Manufacturer $manufacturer
     * @return $this
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
        return $this;
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
     * @return $this
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
        return $this;
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
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return \Shopware\Struct\Media[]
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param \Shopware\Struct\Media[] $media
     * @return $this
     */
    public function setMedia($media)
    {
        $this->media = $media;
        return $this;
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

    /**
     * @return boolean
     */
    public function getCloseouts()
    {
        return $this->closeouts;
    }

    /**
     * @param boolean $closeouts
     * @return $this
     */
    public function setCloseouts($closeouts)
    {
        $this->closeouts = $closeouts;
        return $this;
    }
}