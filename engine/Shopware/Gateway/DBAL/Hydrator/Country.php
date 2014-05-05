<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct\Country\Area;
use Shopware\Struct\Country\State;

class Country extends Hydrator
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
     * @return \Shopware\Struct\Country\State
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
}