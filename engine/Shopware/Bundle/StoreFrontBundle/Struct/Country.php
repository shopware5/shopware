<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Country extends Extendable implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $iso;

    /**
     * @var string
     */
    protected $en;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $iso3;

    /**
     * @var boolean
     */
    protected $shippingFree;

    /**
     * @var boolean
     */
    protected $taxFree;

    /**
     * @var boolean
     */
    protected $taxFreeForVatId;

    /**
     * @var boolean
     */
    protected $vatIdCheck;

    /**
     * @var boolean
     */
    protected $displayStateSelection;

    /**
     * @var boolean
     */
    protected $requiresStateSelection;

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

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
