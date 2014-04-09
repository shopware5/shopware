<?php

namespace Shopware\Hydrator\DBAL;

use Shopware\Struct as Struct;

class Product
{
    /**
     * @var Manufacturer
     */
    private $manufacturerHydrator;

    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @var Tax
     */
    private $taxHydrator;

    /**
     * @var Unit
     */
    private $unitHydrator;

    function __construct(
        Attribute $attributeHydrator,
        Manufacturer $manufacturerHydrator,
        Tax $taxHydrator,
        Unit $unitHydrator
    )
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->taxHydrator = $taxHydrator;
        $this->unitHydrator = $unitHydrator;
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
            $product->setId(intval($data['id']));
        }

        if (isset($data['variantId'])) {
            $product->setVariantId(intval($data['variantId']));
        }

        if (isset($data['name'])) {
            $product->setName($data['name']);
        }

        if (isset($data['ordernumber'])) {
            $product->setNumber($data['ordernumber']);
        }

        if (isset($data['description'])) {
            $product->setShortDescription($data['description']);
        }

        if (isset($data['description_long'])) {
            $product->setLongDescription($data['description_long']);
        }

        if (isset($data['shippingtime'])) {
            $product->setShippingTime($data['shippingtime']);
        }

        if (isset($data['shippingfree'])) {
            $product->setShippingFree((bool)($data['shippingfree']));
        }

        if (isset($data['laststock'])) {
            $product->setCloseouts((bool)($data['laststock']));
        }

        if (isset($data['instock'])) {
            $product->setStock(intval($data['instock']));
        }

        if (isset($data['releasedate'])) {
            $product->setReleaseDate(
                new \DateTime($data['releasedate'])
            );
        }

        if (isset($data['filtergroupID'])) {
            $product->setHasProperties($data['filtergroupID'] > 0);
        }

        if (isset($data['priceGroup'])) {
            $product->setPriceGroup(new Struct\PriceGroup());

            $product->getPriceGroup()->setId($data['priceGroup']['id']);
            $product->getPriceGroup()->setName($data['priceGroup']['description']);
        }

        if (isset($data['topseller'])) {
            $product->setHighlight((bool)($data['topseller']));
        }

        if (isset($data['notification'])) {
            $product->setAllowsNotification((bool)($data['notification']));
        }

        if (isset($data['additionaltext'])) {
            $product->setAdditional($data['additionaltext']);
        }

        if (isset($data['ean'])) {
            $product->setEan($data['ean']);
        }

        if (isset($data['height'])) {
            $product->setHeight(floatval($data['height']));
        }

        if (isset($data['keywords'])) {
            $product->setKeywords($data['keywords']);
        }

        if (isset($data['length'])) {
            $product->setLength(floatval($data['length']));
        }

        if (isset($data['maxpurchase'])) {
            $product->setMaxPurchase(intval($data['maxpurchase']));
        }

        if (isset($data['minpurchase'])) {
            $product->setMinPurchase(intval($data['minpurchase']));
        }

        if (isset($data['stockmin'])) {
            $product->setMinStock(intval($data['stockmin']));
        }

        if (isset($data['purchasesteps'])) {
            $product->setPurchaseStep(intval($data['purchasesteps']));
        }

        if (isset($data['weight'])) {
            $product->setWeight(floatval($data['weight']));
        }

        if (isset($data['width'])) {
            $product->setWidth(floatval($data['width']));
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
        $tax = $this->taxHydrator->hydrate(
            $data['tax']
        );

        $product->setTax($tax);
    }

    private function assignUnitData(Struct\ProductMini $product, $data)
    {
        $data['unit']['packunit'] = $data['packunit'];
        $data['unit']['purchaseunit'] = $data['purchaseunit'];
        $data['unit']['referenceunit'] = $data['referenceunit'];

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