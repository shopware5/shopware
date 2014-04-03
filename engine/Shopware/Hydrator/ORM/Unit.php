<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Unit
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Unit
     */
    public function hydrate(array $data)
    {
        $unit = new Struct\Unit();

        $this->assignUnitData($unit, $data);

        return $unit;
    }

    /**
     * Assigns the passed data array to the passed unit instance.
     *
     * @param Struct\Unit $unit
     * @param array $data
     */
    public function assignUnitData(Struct\Unit $unit, array $data)
    {
        if (isset($data['id'])) {
            $unit->setId($data['id']);
        }

        if (isset($data['name'])) {
            $unit->setName($data['name']);
        }

        if (isset($data['unit'])) {
            $unit->setUnit($data['unit']);
        }

        if (isset($data['packUnit'])) {
            $unit->setPackUnit($data['packUnit']);
        }

        if (isset($data['purchaseUnit'])) {
            $unit->setPurchaseUnit(floatval($data['purchaseUnit']));
        }

        if (isset($data['referenceUnit'])) {
            $unit->setReferenceUnit(floatval($data['referenceUnit']));
        }

    }
}