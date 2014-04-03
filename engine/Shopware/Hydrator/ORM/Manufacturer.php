<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Manufacturer
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Manufacturer
     */
    public function hydrate(array $data)
    {
        $manufacturer = new Struct\Manufacturer();

        $this->assignManufacturerData($manufacturer, $data);

        return $manufacturer;
    }

    /**
     * @param Struct\Manufacturer $manufacturer
     * @param array $data
     */
    public function assignManufacturerData(Struct\Manufacturer $manufacturer, array $data)
    {
        if (isset($data['id'])) {
            $manufacturer->setId($data['id']);
        }

        if (isset($data['name'])) {
            $manufacturer->setName($data['name']);
        }

        if (isset($data['description'])) {
            $manufacturer->setDescription($data['description']);
        }

        if (isset($data['metaTitle'])) {
            $manufacturer->setMetaTitle($data['metaTitle']);
        }

        if (isset($data['metaDescription'])) {
            $manufacturer->setMetaDescription($data['metaDescription']);
        }

        if (isset($data['metaKeywords'])) {
            $manufacturer->setMetaKeywords($data['metaKeywords']);
        }
    }
}