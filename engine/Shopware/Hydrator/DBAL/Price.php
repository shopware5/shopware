<?php

namespace Shopware\Hydrator\DBAL;
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
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @param CustomerGroup $customerGroupHydrator
     * @param Unit $unitHydrator
     * @param Attribute $attributeHydrator
     */
    function __construct(
        CustomerGroup $customerGroupHydrator,
        Unit $unitHydrator,
        Attribute $attributeHydrator
    ) {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->unitHydrator = $unitHydrator;
        $this->attributeHydrator = $attributeHydrator;

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

        $price->setPseudoPrice(floatval($data['pseudoprice']));

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

        if (isset($data['attribute'])) {
            $price->addAttribute(
                'core',
                $this->attributeHydrator->hydrate($data['attribute'])
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

        $unit = $data['detail']['unit'];

        $unit['packunit'] = $data['detail']['packunit'];
        $unit['purchaseunit'] = $data['detail']['purchaseunit'];
        $unit['referenceunit'] = $data['detail']['referenceunit'];

        $unit['minpurchase'] = $data['detail']['minpurchase'];
        $unit['maxpurchase'] = $data['detail']['maxpurchase'];
        $unit['purchasesteps'] = $data['detail']['purchasesteps'];

        $price->setUnit(
            $this->unitHydrator->hydrate($unit)
        );

        return $price;
    }
}