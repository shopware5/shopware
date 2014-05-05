<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Price extends Hydrator
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
     * @return \Shopware\Struct\Product\PriceRule
     */
    public function hydratePriceRule(array $data)
    {
        $price = new Struct\Product\PriceRule();

        $price->setId($data['id']);

        $price->setFrom($data['from']);

        $price->setPrice(floatval($data['price']));

        $price->setPseudoPrice(floatval($data['pseudoprice']));

        if (strtolower($data['to']) == 'beliebig') {
            $price->setTo(null);
        } else {
            $price->setTo($data['to']);
        }

        if (isset($data['__attribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
            );

            $price->addAttribute('core', $attribute);
        }

        return $price;
    }

    /**
     * Hydrates the data result of the cheapest price query.
     * This function uses the normally hydrate function of this class
     * and adds additionally the product unit information to the price.
     *
     * @param array $data
     * @return Struct\Product\PriceRule
     */
    public function hydrateCheapestPrice(array $data)
    {
        $price = $this->hydratePriceRule($data);

        $unit = $this->unitHydrator->hydrate(
            array(
                'id' => $data['__unit_id'],
                'description' => $data['__unit_description'],
                'unit' => $data['__unit_unit'],
                'packunit' => $data['__unit_packunit'],
                'purchaseunit' => $data['__unit_purchaseunit'],
                'referenceunit' => $data['__unit_referenceunit'],
                'purchasesteps' => $data['__unit_purchasesteps'],
                'minpurchase' => $data['__unit_minpurchase'],
                'maxpurchase' => $data['__unit_maxpurchase'],
            )
        );

        $price->setUnit($unit);

        return $price;
    }

    public function hydratePriceDiscount(array $data)
    {
        $discount = new Struct\Product\PriceDiscount();

        $discount->setId($data['id']);

        $discount->setPercent(floatval($data['discount']));

        $discount->setQuantity(intval($data['discountstart']));

        return $discount;
    }
}