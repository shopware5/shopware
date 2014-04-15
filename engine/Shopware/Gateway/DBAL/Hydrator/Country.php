<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct\Area;
use Shopware\Struct\State;

class Country
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return Area
     */
    public function hydrateArea(array $data)
    {
        $area = new Area();

        $area->setId($data['id']);

        $area->setName($data['name']);

        return $area;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Country
     */
    public function hydrateCountry(array $data)
    {
        $country = new \Shopware\Struct\Country();

        $country->setId($data['id']);

        $country->setName($data['name']);

        if ($data['__attribute_id'] !== null) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
            );
            $country->addAttribute('core', $attribute);
        }

        return $country;
    }

    /**
     * @param array $data
     */
    public function hydrateState(array $data)
    {
        $state = new State();

        $state->setId($data['id']);

        if ($data['__attribute_id'] !== null) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
            );
            $state->addAttribute('core', $attribute);
        }

        return $state;
    }


    private function extractFields($prefix, $data)
    {
        $result = array();
        foreach($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $key = str_replace($prefix, '', $field);
                $result[$key] = $value;
            }
        }
        return $result;
    }
}