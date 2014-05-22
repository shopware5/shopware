<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Product extends Hydrator
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
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->taxHydrator = $taxHydrator;
        $this->unitHydrator = $unitHydrator;
    }

    public function hydrateProduct(array $data)
    {
        $product = new Struct\Product();

        return $this->assignData($product, $data);
    }

    /**
     * Hydrates the passed data and converts the ORM
     * array values into a Struct\ListProduct class.
     *
     * @param array $data
     * @return Struct\ListProduct
     */
    public function hydrateListProduct(array $data)
    {
        $product = new Struct\ListProduct();

        return $this->assignData($product, $data);
    }

    protected function assignData(Struct\ListProduct $product, array $data)
    {
        $this->assignProductData($product, $data);

        $this->assignTaxData($product, $data);

        if (!empty($data['supplierID'])) {
            $this->assignManufacturerData($product, $data);
        }

        $this->assignUnitData($product, $data);

        if (!empty($data['__attribute_id'])) {
            $this->assignAttributeData($product, $data);
        }

        return $product;
    }

    /**
     * Helper function which assigns the shopware article
     * data to the product. (data of s_articles)
     *
     * @param Struct\ListProduct $product
     * @param $data
     */
    public function assignProductData(Struct\ListProduct $product, $data)
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

        if (!empty($data['__priceGroup_id'])) {
            $product->setPriceGroup(new Struct\Product\PriceGroup());
            $product->getPriceGroup()->setId($data['__priceGroup_id']);
            $product->getPriceGroup()->setName($data['__priceGroup_description']);
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

        if (isset($data['stockmin'])) {
            $product->setMinStock(intval($data['stockmin']));
        }

        if (isset($data['weight'])) {
            $product->setWeight(floatval($data['weight']));
        }

        if (isset($data['width'])) {
            $product->setWidth(floatval($data['width']));
        }
    }

    private function assignManufacturerData(Struct\ListProduct $product, $data)
    {
        $manufacturer = $this->extractFields('__manufacturer_', $data);

        if (!empty($data['__manufacturerAttribute_id'])) {
            $manufacturer = array_merge(
                $manufacturer,
                $this->getFields('__manufacturerAttribute_', $data)
            );
        }

        $manufacturer = $this->manufacturerHydrator->hydrate($manufacturer);

        $product->setManufacturer($manufacturer);
    }

    private function assignTaxData(Struct\ListProduct $product, $data)
    {
        $tax = $this->taxHydrator->hydrate(
            array(
                'id' => $data['__tax_id'],
                'tax' => $data['__tax_tax'],
                'description' => $data['__tax_description'],
            )
        );

        $product->setTax($tax);
    }

    private function assignUnitData(Struct\ListProduct $product, $data)
    {
        $unit = $this->unitHydrator->hydrate(
            array(
                'id' => $data['__unit_id'],
                'description' => $data['__unit_description'],
                'unit' => $data['__unit_unit'],
                'packunit' => $data['packunit'],
                'purchaseunit' => $data['purchaseunit'],
                'referenceunit' => $data['referenceunit'],
                'purchasesteps' => $data['purchasesteps'],
                'minpurchase' => $data['minpurchase'],
                'maxpurchase' => $data['maxpurchase'],
            )
        );

        $product->setUnit($unit);
    }

    private function assignAttributeData(Struct\ListProduct $product, $data)
    {
        $attribute = $this->attributeHydrator->hydrate(
            $this->extractFields('__attribute_', $data)
        );

        $product->addAttribute('core', $attribute);
    }

}