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

        $customerGroup->setId(intval($data['__customerGroup_id']));

        $customerGroup->setName($data['__customerGroup_description']);

        $customerGroup->setDisplayGrossPrices((bool) ($data['__customerGroup_tax']));

        $customerGroup->setKey($data['__customerGroup_groupkey']);

        $customerGroup->setMinimumOrderValue((float) $data['__customerGroup_minimumorder']);

        $customerGroup->setPercentageDiscount((float) $data['__customerGroup_discount']);

        $customerGroup->setSurcharge((float) $data['__customerGroup_minimumordersurcharge']);

        $customerGroup->setUseDiscount((bool) ($data['__customerGroup_mode']));

        if (!empty($data['__customerGroupAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__customerGroupAttribute_', $data)
            );
            $customerGroup->addAttribute('core', $attribute);
        }

        return $customerGroup;
    }
}
