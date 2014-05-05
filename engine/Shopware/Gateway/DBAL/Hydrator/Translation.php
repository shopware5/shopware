<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Translation extends Hydrator
{
    /**
     * Contains the translation field mapping
     * for a product translation.
     *
     * @var array
     */
    private $productMapping = array(
        'txtshortdescription' => 'description',
        'txtlangbeschreibung' => 'description_long',
        'txtArtikel' => 'name',
        'txtzusatztxt' => 'additionaltext',
        'txtkeywords' => 'keywords',
        'txtpackunit' => 'packUnit'
    );

    /**
     * @var Product
     */
    private $productHydrator;

    /**
     * @var Unit
     */
    private $unitHydrator;

    /**
     * @var Manufacturer
     */
    private $manufacturerHydrator;

    /**
     * @var Property
     */
    private $propertyHydrator;


    function __construct(
        Product $productHydrator,
        Unit $unitHydrator,
        Manufacturer $manufacturerHydrator,
        Property $propertyHydrator
    ) {
        $this->productHydrator = $productHydrator;
        $this->unitHydrator = $unitHydrator;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->propertyHydrator = $propertyHydrator;
    }

    /**
     * @param \Shopware\Struct\ListProduct $product
     * @param array $data
     */
    public function hydrateProductTranslation(Struct\ListProduct $product, array $data)
    {
        $data = $this->mapArray($data, $this->productMapping);

        $this->productHydrator->assignProductData($product, $data);

        if ($product->getUnit() && $data['packUnit']) {
            $product->getUnit()->setPackUnit(
                $data['packUnit']
            );
        }

        if ($product->hasAttribute('core')) {
            $this->mergeAttributeTranslation(
                $data,
                $product->getAttribute('core')
            );
        }
    }

    /**
     * @param Struct\Product\Unit $unit
     * @param array $data
     */
    public function hydrateUnitTranslation(Struct\Product\Unit $unit, array $data)
    {
        $this->unitHydrator->assignUnitData(
            $unit,
            $data
        );
    }

    /**
     * @param Struct\Product\Manufacturer $manufacturer
     * @param array $data
     */
    public function hydrateManufacturerTranslation(Struct\Product\Manufacturer $manufacturer, array $data)
    {
        $this->manufacturerHydrator->assignData(
            $manufacturer,
            $data
        );
    }

    /**
     * This function translates the passed property set and the associated groups
     * and options.
     *
     * The function expects the following array structure:
     *
     * array(
     *    [objectdata] => a:1:{s:9:"groupName";s:6:"Brandy";}   (s_filter translation)
     *    [groups] = array(
     *       //id of property group as array key   (s_filter_options translation)
     *       [6] => 'a:1:{s:10:"optionName";s:5:"Taste";}'
     *       [7] => 'a:1:{s:10:"optionName";s:8:"Serve at";}'
     *    )
     *     [options] => array(
     *       //id of property option as array key  (s_filter_values translation)
     *       [28] => a:1:{s:11:"optionValue";s:3:"red";}
     *       [29] => a:1:{s:11:"optionValue";s:11:"transparent";}
     * )
     *
     * @param Struct\Property\Set $set
     * @param array $data
     */
    public function hydratePropertyTranslation(Struct\Property\Set $set, array $data)
    {
        if (isset($data['groupName'])) {
            $set->setName($data['groupName']);
        }

        foreach ($set->getGroups() as $group) {
            $translation = $data['groups'][$group->getId()];

            if ($translation) {
                $translation = unserialize($translation);

                if (isset($translation['optionName'])) {
                    $group->setName($translation['optionName']);
                }
            }

            foreach ($group->getOptions() as $option) {
                $translation = $data['options'][$option->getId()];

                if ($translation) {
                    $translation = unserialize($translation);

                    if (isset($translation['optionValue'])) {
                        $option->setName($translation['optionValue']);
                    }
                }
            }
        }
    }

    private function mapArray($data, $mapping)
    {
        foreach ($mapping as $old => $new) {
            if (!isset($data[$old])) {
                continue;
            }
            $data[$new] = $data[$old];
            unset($data[$old]);
        }

        return $data;
    }

    private function mergeAttributeTranslation(array $translation, Struct\Attribute $attribute)
    {
        foreach ($translation as $key => $value) {
            if (!$attribute->exists($key)) {
                continue;
            }
            $attribute->set($key, $value);
        }
    }
}