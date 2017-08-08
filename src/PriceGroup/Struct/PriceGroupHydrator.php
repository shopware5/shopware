<?php

namespace Shopware\PriceGroup\Struct;

use Shopware\PriceGroupDiscount\Struct\PriceGroupDiscountCollection;
use Shopware\PriceGroupDiscount\Struct\PriceGroupDiscountHydrator;

class PriceGroupHydrator
{
    /**
     * @var PriceGroupDiscountHydrator
     */
    private $discountHydrator;

    public function __construct(PriceGroupDiscountHydrator $discountHydrator)
    {
        $this->discountHydrator = $discountHydrator;
    }

    public function hydrate($data): PriceGroup
    {
        $group = new PriceGroup();

        $first = $data[0];

        $group->setId((int) $first['__priceGroup_id']);
        $group->setName($first['__priceGroup_description']);

        $discounts = new PriceGroupDiscountCollection();
        foreach ($data as $row) {
            $discounts->add($this->discountHydrator->hydrate($row));
        }

        $group->setDiscounts($discounts);

        return $group;
    }
}