<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Currency extends Base
{
    private $id;

    private $name;

    private $currency;

    private $factor;

    private $symbol;

    /**
     * @param mixed $id
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
     * @param mixed $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $factor
     *
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

    }

    /**
     * @return mixed
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param mixed $currency
     *
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $symbol
     *
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }


}