<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Price
{
    /**
     * @var CustomerGroup
     */
    private $customerGroupHydrator;

    /**
     * @var Unit
     */
    private $unitHydrator;

    /**
     * @param CustomerGroup $customerGroupHydrator
     * @param Unit $unitHydrator
     */
    function __construct(CustomerGroup $customerGroupHydrator, Unit $unitHydrator)
    {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->unitHydrator = $unitHydrator;
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

        $price->setPrice(floatval($data['price']));

        $price->setPseudoPrice(floatval($data['pseudoPrice']));

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


    /**
     * Hydrates the data result of the cheapest price query.
     * This function uses the normally hydrate function of this class
     * and adds additionally the product unit information to the price.
     *
     * @param array $data
     * @return Struct\Price
     */
    public function hydrateCheapestPrice(array $data)
    {
        $price = $this->hydrate($data);

        if (empty($data['detail']['unit'])) {
            return $price;
        }

        $unit = $data['detail']['unit'];

        $unit['packUnit'] = $data['detail']['packUnit'];
        $unit['purchaseUnit'] = $data['detail']['purchaseUnit'];
        $unit['referenceUnit'] = $data['detail']['referenceUnit'];

        $price->setUnit(
            $this->unitHydrator->hydrate($unit)
        );

        return $price;
    }
}