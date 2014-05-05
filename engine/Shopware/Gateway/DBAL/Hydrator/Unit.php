<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Unit extends Hydrator
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Product\Unit
     */
    public function hydrate(array $data)
    {
        $unit = new Struct\Product\Unit();

        $this->assignUnitData($unit, $data);

        return $unit;
    }

    /**
     * Assigns the passed data array to the passed unit instance.
     *
     * @param Struct\Product\Unit $unit
     * @param array $data
     */
    public function assignUnitData(Struct\Product\Unit $unit, array $data)
    {
        if (isset($data['id'])) {
            $unit->setId(intval($data['id']));
        }

        if (isset($data['description'])) {
            $unit->setName($data['description']);
        }

        if (isset($data['unit'])) {
            $unit->setUnit($data['unit']);
        }

        if (isset($data['packunit'])) {
            $unit->setPackUnit($data['packunit']);
        }

        if (isset($data['purchaseunit'])) {
            $unit->setPurchaseUnit(floatval($data['purchaseunit']));
        }

        if (isset($data['referenceunit'])) {
            $unit->setReferenceUnit(floatval($data['referenceunit']));
        }

        if (isset($data['purchasesteps'])) {
            $unit->setPurchaseStep(intval($data['purchasesteps']));
        }

        if (isset($data['minpurchase'])) {
            $unit->setMinPurchase(intval($data['minpurchase']));
        }

        if (isset($data['maxpurchase'])) {
            $unit->setMaxPurchase(intval($data['maxpurchase']));
        }
    }
}