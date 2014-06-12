<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Country extends Extendable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $iso;

    /**
     * @var string
     */
    private $en;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $iso3;

    /**
     * @var boolean
     */
    private $shippingFree;

    /**
     * @var boolean
     */
    private $taxFree;

    /**
     * @var boolean
     */
    private $taxFreeForVatId;

    /**
     * @var boolean
     */
    private $vatIdCheck;

    /**
     * @var boolean
     */
    private $displayStateSelection;

    /**
     * @var boolean
     */
    private $requiresStateSelection;


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @param string $iso
     */
    public function setIso($iso)
    {
        $this->iso = $iso;
    }

    /**
     * @return string
     */
    public function getEn()
    {
        return $this->en;
    }

    /**
     * @param string $en
     */
    public function setEn($en)
    {
        $this->en = $en;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    }

    /**
     * @param boolean $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
    }

    /**
     * @param boolean $taxFree
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;
    }

    /**
     * @param boolean $taxFreeForVatId
     */
    public function setTaxFreeForVatId($taxFreeForVatId)
    {
        $this->taxFreeForVatId = $taxFreeForVatId;
    }

    /**
     * @param boolean $vatIdCheck
     */
    public function setVatIdCheck($vatIdCheck)
    {
        $this->vatIdCheck = $vatIdCheck;
    }

    /**
     * @param boolean $displayStateSelection
     */
    public function setDisplayStateSelection($displayStateSelection)
    {
        $this->displayStateSelection = $displayStateSelection;
    }

    /**
     * @param boolean $requiresStateSelection
     */
    public function setRequiresStateSelection($requiresStateSelection)
    {
        $this->requiresStateSelection = $requiresStateSelection;
    }

    /**
     * @return boolean
     */
    public function isShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @return boolean
     */
    public function isTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @return boolean
     */
    public function isTaxFreeForVatId()
    {
        return $this->taxFreeForVatId;
    }

    /**
     * @return boolean
     */
    public function checkVatId()
    {
        return $this->vatIdCheck;
    }

    /**
     * @return boolean
     */
    public function displayStateSelection()
    {
        return $this->displayStateSelection;
    }

    /**
     * @return boolean
     */
    public function requiresStateSelection()
    {
        return $this->requiresStateSelection;
    }


}
