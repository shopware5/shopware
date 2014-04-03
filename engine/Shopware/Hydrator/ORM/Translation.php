<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Translation
{
    /**
     * Contains the translation field mapping
     * for a product translation.
     *
     * @var array
     */
    private $productMapping = array(
        'txtshortdescription' => 'shortDescription',
        'txtlangbeschreibung' => 'longDescription',
        'txtArtikel' => 'name',
        'txtzusatztxt' => 'additional',
        'txtkeywords' => 'keywords',
        'txtpackunit' => 'packUnit'
    );

    private $unitMapping = array(
        'description' => 'name'
    );

    private $propertySetTranslationMapping = array(
        'groupName' => 'name'
    );

    private $propertyGroupTranslationMapping = array(
        'optionName' => 'name'
    );

    private $propertyOptionTranslationMapping = array(
        'optionValue' => 'name'
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
     * @param \Shopware\Struct\ProductMini $product
     * @param array $data
     */
    public function hydrateProductTranslation(Struct\ProductMini $product, array $data)
    {
        $data = unserialize($data['data']);

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
     * @param Struct\Unit $unit
     * @param array $data
     */
    public function hydrateUnitTranslation(Struct\Unit $unit, array $data)
    {
        $data = unserialize($data['data']);
        $data = $data[1];

        $data = $this->mapArray($data, $this->unitMapping);

        $this->unitHydrator->assignUnitData($unit, $data);
    }

    /**
     * @param Struct\Manufacturer $manufacturer
     * @param array $data
     */
    public function hydrateManufacturerTranslation(Struct\Manufacturer $manufacturer, array $data)
    {
        $data = unserialize($data['data']);

        $this->manufacturerHydrator->assignManufacturerData(
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
     * @param Struct\PropertySet $set
     * @param array $data
     */
    public function hydratePropertyTranslation(Struct\PropertySet $set, array $data)
    {
        $translation = unserialize($data['objectdata']);

        $translation = $this->mapArray(
            $translation,
            $this->propertySetTranslationMapping
        );

        $this->propertyHydrator->assignSetData($set, $translation);

        foreach($set->getGroups() as $group) {
            $translation = $this->extractTranslation(
                $data['groups'],
                $group->getId(),
                $this->propertyGroupTranslationMapping
            );

            if ($translation) {
                $this->propertyHydrator->assignGroupData(
                    $group, $translation
                );
            }

            foreach($group->getOptions() as $option) {
                $translation = $this->extractTranslation(
                    $data['options'],
                    $option->getId(),
                    $this->propertyOptionTranslationMapping
                );

                $this->propertyHydrator->assignOptionData(
                    $option, $translation
                );
            }
        }
    }

    /**
     * Helper function which extracts an object translation for the properties.
     *
     * @param $data
     * @param $key
     * @param $mapping
     * @return array
     */
    private function extractTranslation($data, $key, $mapping)
    {
        $translation = $data[$key];

        if (!$translation) {
            return array();
        }

        $translation = unserialize($translation);

        return $this->mapArray(
            $translation,
            $mapping
        );
    }

    private function mapArray($data, $mapping)
    {
        foreach($mapping as $old => $new) {
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
        foreach($translation as $key => $value) {
            if (!$attribute->exists($key)) {
                continue;
            }
            $attribute->set($key, $value);
        }
    }
}