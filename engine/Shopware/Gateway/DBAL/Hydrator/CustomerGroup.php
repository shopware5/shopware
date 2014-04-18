<?php

namespace Shopware\Gateway\DBAL\Hydrator;
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

    /**
     * @param $prefix
     * @param $data
     * @return array
     */
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