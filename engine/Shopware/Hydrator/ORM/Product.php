<?php

namespace Shopware\Hydrator\ORM;

use Shopware\Struct as Struct;

class Product
{
    /**
     * Used to hydrate the price data and converts the array
     * into a Struct\Price
     *
     * @var Price
     */
    private $priceHydrator;

    /**
     * @var Manufacturer
     */
    private $manufacturerHydrator;

    /**
     * @var Tax
     */
    private $taxHydrator;

    /**
     * @var Unit
     */
    private $unitHydrator;

    /**
     * @var Media
     */
    private $mediaHydrator;

    /**
     * @var Attribute
     */
    private $attributeHydrator;


    /**
     * @param Price $priceHydrator
     * @param Manufacturer $manufacturerHydrator
     * @param Tax $taxHydrator
     * @param Unit $unitHydrator
     * @param Media $mediaHydrator
     * @param Attribute $attributeHydrator
     */
    function __construct(
        Price $priceHydrator,
        Manufacturer $manufacturerHydrator,
        Tax $taxHydrator,
        Unit $unitHydrator,
        Media $mediaHydrator,
        Attribute $attributeHydrator
    ) {
        $this->priceHydrator = $priceHydrator;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->taxHydrator = $taxHydrator;
        $this->unitHydrator = $unitHydrator;
        $this->mediaHydrator = $mediaHydrator;
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * Hydrates the passed data and converts the ORM
     * array values into a Struct\ProductMini class.
     *
     * @param array $data
     * @return Struct\ProductMini
     */
    public function hydrateMini(array $data)
    {
        $product = new Struct\ProductMini();

        $this->assignProductData($product, $data);

        $this->assignTaxData($product, $data);

        $this->assignMediaData($product, $data);


        if (isset($data['supplier'])) {
            $this->assignManufacturerData($product, $data);
        }

        if (isset($data['prices'])) {
            $this->assignPriceData($product, $data);
        }

        if (isset($data['unit'])) {
            $this->assignUnitData($product, $data);
        }

        if (isset($data['attribute'])) {
            $this->assignAttributeData($product, $data);
        }

        return $product;
    }

    /**
     * Helper function which assigns the shopware article
     * data to the product. (data of s_articles)
     *
     * @param Struct\ProductMini $product
     * @param $data
     */
    private function assignProductData(Struct\ProductMini $product, $data)
    {
        $product->setId($data['id']);

        $product->setName($data['name']);

        $product->setNumber($data['number']);

        $product->setShortDescription($data['description']);

        $product->setLongDescription($data['descriptionLong']);

        $product->setShippingTime($data['shippingTime']);

        $product->setShippingFree($data['shippingFree']);

        $product->setCloseouts($data['lastStock']);

        $product->setStock($data['inStock']);

        $product->setPackUnit($data['packUnit']);

        $product->setPurchaseUnit($data['purchaseUnit']);

        $product->setReferenceUnit($data['referenceUnit']);

        $product->setReleaseDate($data['releaseDate']);
    }

    private function assignManufacturerData(Struct\ProductMini $product, $data)
    {
        $manufacturer = $this->manufacturerHydrator->hydrate(
            $data['supplier']
        );

        $product->setManufacturer($manufacturer);
    }

    private function assignPriceData(Struct\ProductMini $product, $data)
    {
        $prices = array();

        foreach($data['prices'] as $price) {
            $prices[] = $this->priceHydrator->hydrate($price);
        }

        $product->setPrices($prices);
    }

    private function assignTaxData(Struct\ProductMini $product, $data)
    {
        $tax = $this->taxHydrator->hydrate($data['tax']);

        $product->setTax($tax);
    }

    private function assignMediaData(Struct\ProductMini $product, $data)
    {
        $media = array();

        foreach($data['images'] as $image) {
            $media[] = $this->mediaHydrator->hydrateProductImage($image);
        }

        $product->setMedia($media);
    }

    private function assignUnitData(Struct\ProductMini $product, $data)
    {
        $unit = $this->unitHydrator->hydrate(
            $data['unit']
        );

        $product->setUnit($unit);
    }

    private function assignAttributeData(Struct\ProductMini $product, $data)
    {
        $attribute = $this->attributeHydrator->hydrate(
            $data['attribute']
        );

        $product->addAttribute('core', $attribute);
    }
}