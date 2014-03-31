<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Price
{
    /**
     * @var int
     */
    private $id;

    /**
     * Price value of the product price struct.
     *
     * @var float
     */
    private $price;

    /**
     * @var int
     */
    private $from;

    /**
     * @var null|int
     */
    private $to = null;

    /**
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    private $pseudoPrice;

    /**
     * Contains the calculated gross or net price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price
     *
     * @var float
     */
    private $calculatedPrice;

    /**
     * Contains the calculated reference unit price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price.
     *
     * The reference unit price is calculated over the price value
     * and the pack and reference unit of the product.
     *
     * @var float
     */
    private $calculatedReferencePrice;

    /**
     * Contains the calculated pseudo price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price.
     *
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    private $calculatedPseudoPrice;

    /**
     * @param mixed $id
     *
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $price
     *
     */
    public function setPrice($price)
    {
        $this->price = $price;

    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $from
     *
     */
    public function setFrom($from)
    {
        $this->from = $from;

    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param null $to
     *
     */
    public function setTo($to)
    {
        $this->to = $to;

    }

    /**
     * @return null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param float $pseudoPrice
     *
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;

    }


    /**
     * @param float $calculatedPrice
     *
     */
    public function setCalculatedPrice($calculatedPrice)
    {
        $this->calculatedPrice = $calculatedPrice;

    }

    /**
     * @return float
     */
    public function getCalculatedPrice()
    {
        return $this->calculatedPrice;
    }

    /**
     * @return float
     */
    public function getCalculatedReferencePrice()
    {
        return $this->calculatedReferencePrice;
    }

    /**
     * @param float $calculatedReferencePrice
     *
     */
    public function setCalculatedReferencePrice($calculatedReferencePrice)
    {
        $this->calculatedReferencePrice = $calculatedReferencePrice;

    }

    /**
     * @return float
     */
    public function getCalculatedPseudoPrice()
    {
        return $this->calculatedPseudoPrice;
    }

    /**
     * @param float $calculatedPseudoPrice
     *
     */
    public function setCalculatedPseudoPrice($calculatedPseudoPrice)
    {
        $this->calculatedPseudoPrice = $calculatedPseudoPrice;

    }


}