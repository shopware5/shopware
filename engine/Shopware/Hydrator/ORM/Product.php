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

        if (isset($data['supplier'])) {
            $this->assignManufacturerData($product, $data);
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
    public function assignProductData(Struct\ProductMini $product, $data)
    {
        if (isset($data['id'])) {
            $product->setId($data['id']);
        }

        if (isset($data['variantId'])) {
            $product->setVariantId($data['variantId']);
        }

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['number'])) {
            $product->setNumber($data['number']);
        }

        if (isset($data['description'])) {
            $product->setShortDescription($data['description']);
        }

        if (isset($data['descriptionLong'])) {
            $product->setLongDescription($data['descriptionLong']);
        }

        if (isset($data['shippingTime'])) {
            $product->setShippingTime($data['shippingTime']);
        }

        if (isset($data['shippingFree'])) {
            $product->setShippingFree($data['shippingFree']);
        }

        if (isset($data['lastStock'])) {
            $product->setCloseouts($data['lastStock']);
        }

        if (isset($data['inStock'])) {
            $product->setStock($data['inStock']);
        }

        if (isset($data['releaseDate'])) {
            $product->setReleaseDate($data['releaseDate']);
        }
    }

    private function assignManufacturerData(Struct\ProductMini $product, $data)
    {
        $manufacturer = $this->manufacturerHydrator->hydrate(
            $data['supplier']
        );

        $product->setManufacturer($manufacturer);
    }

    private function assignTaxData(Struct\ProductMini $product, $data)
    {
        $tax = $this->taxHydrator->hydrate($data['tax']);

        $product->setTax($tax);
    }

    private function assignUnitData(Struct\ProductMini $product, $data)
    {
        $data['unit']['packUnit'] = $data['packUnit'];
        $data['unit']['purchaseUnit'] = $data['purchaseUnit'];
        $data['unit']['referenceUnit'] = $data['referenceUnit'];

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