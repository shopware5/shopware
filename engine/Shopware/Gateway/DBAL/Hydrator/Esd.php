<?php

namespace Shopware\Gateway\DBAL\Hydrator;

class Esd extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @param Attribute $attributeHydrator
     */
    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Product\Esd
     */
    public function hydrate(array $data)
    {
        $esd = new \Shopware\Struct\Product\Esd();

        if (isset($data['__esd_id'])) {
            $esd->setId($data['__esd_id']);
        }

        if (isset($data['__esd_datum'])) {
            $esd->setCreatedAt(new \DateTime($data['__esd_datum']));
        }

        if (isset($data['__esd_file'])) {
            $esd->setFile($data['__esd_file']);
        }

        if (isset($data['__esd_serials'])) {
            $esd->setHasSerials((bool)$data['__esd_serials']);
        }

        return $esd;
    }

}