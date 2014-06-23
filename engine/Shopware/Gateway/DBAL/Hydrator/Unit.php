<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Unit extends Hydrator
{
    /**
     * @var array
     */
    private $translationMapping = array(
        'unit' => '__unit_unit',
        'description' => '__unit_description'
    );

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
     * Extracts and unserialize the unit translation
     *
     * @param $data
     * @return array|mixed
     */
    private function getTranslation($data)
    {
        $translation = array();

        if (!isset($data['__unit_translation'])) {
            return $translation;
        }

        $result = unserialize($data['__unit_translation']);

        $translation = $result[$data['__unit_id']];

        if (!is_array($translation)) {
            return array();
        }

        return $this->convertArrayKeys(
            $translation,
            $this->translationMapping
        );
    }

    /**
     * Assigns the passed data array to the passed unit instance.
     *
     * @param Struct\Product\Unit $unit
     * @param array $data
     */
    private function assignUnitData(Struct\Product\Unit $unit, array $data)
    {
        $translation = $this->getTranslation($data);
        $data = array_merge($data, $translation);

        if (isset($data['__unit_id'])) {
            $unit->setId((int) $data['__unit_id']);
        }

        if (isset($data['__unit_description'])) {
            $unit->setName($data['__unit_description']);
        }

        if (isset($data['__unit_unit'])) {
            $unit->setUnit($data['__unit_unit']);
        }

        if (isset($data['__unit_packunit'])) {
            $unit->setPackUnit($data['__unit_packunit']);
        }

        if (isset($data['__unit_purchaseunit'])) {
            $unit->setPurchaseUnit((float) $data['__unit_purchaseunit']);
        }

        if (isset($data['__unit_referenceunit'])) {
            $unit->setReferenceUnit((float) $data['__unit_referenceunit']);
        }

        if (isset($data['__unit_purchasesteps'])) {
            $unit->setPurchaseStep((int) $data['__unit_purchasesteps']);
        }

        if (isset($data['__unit_minpurchase'])) {
            $unit->setMinPurchase((int) $data['__unit_minpurchase']);
        }

        if (isset($data['__unit_maxpurchase'])) {
            $unit->setMaxPurchase((int) $data['__unit_maxpurchase']);
        }
    }
}
