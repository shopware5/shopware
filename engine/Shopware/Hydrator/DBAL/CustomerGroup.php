<?php

namespace Shopware\Hydrator\DBAL;
use Shopware\Struct as Struct;

class CustomerGroup
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }


    public function hydrate(array $data)
    {
        $customerGroup = new Struct\CustomerGroup();

        $customerGroup->setId(intval($data['id']));

        $customerGroup->setName($data['description']);

        $customerGroup->setDisplayGross(boolval($data['tax']));

        $customerGroup->setKey($data['groupkey']);

        $customerGroup->setMinimumOrderValue($data['minimumorder']);

        $customerGroup->setPercentageDiscount(intval($data['discount']));

        $customerGroup->setSurcharge(intval($data['minimumordersurcharge']));

        $customerGroup->setUseDiscount(boolval($data['mode']));

        return $customerGroup;
    }
}