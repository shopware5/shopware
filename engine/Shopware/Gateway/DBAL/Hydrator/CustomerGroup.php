<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class CustomerGroup extends Hydrator
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
     * @return Struct\Customer\Group
     */
    public function hydrate(array $data)
    {
        $customerGroup = new Struct\Customer\Group();

        $customerGroup->setId(intval($data['id']));

        $customerGroup->setName($data['description']);

        $customerGroup->setDisplayGrossPrices((bool)($data['tax']));

        $customerGroup->setKey($data['groupkey']);

        $customerGroup->setMinimumOrderValue($data['minimumorder']);

        $customerGroup->setPercentageDiscount(intval($data['discount']));

        $customerGroup->setSurcharge(intval($data['minimumordersurcharge']));

        $customerGroup->setUseDiscount((bool)($data['mode']));

        if (!empty($data['__attribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
            );
            $customerGroup->addAttribute('core', $attribute);
        }

        return $customerGroup;
    }
}