<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Price
{
    /**
     * @var CustomerGroup
     */
    private $customerGroupHydrator;

    function __construct(CustomerGroup $customerGroupHydrator)
    {
        $this->customerGroupHydrator = $customerGroupHydrator;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Price
     */
    public function hydrate(array $data)
    {
        $price = new Struct\Price();

        $price->setId($data['id']);

        $price->setFrom($data['from']);

        if (strtolower($data['to']) == 'beliebig') {
            $price->setTo(null);
        } else {
            $price->setTo($data['to']);
        }

        if (isset($data['customerGroup'])) {
            $price->setCustomerGroup(
                $this->customerGroupHydrator->hydrate($data['customerGroup'])
            );
        }

        return $price;
    }
}