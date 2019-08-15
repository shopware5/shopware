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

use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;

class Country extends Extendable
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
     * @var int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $iso3;

    /**
     * @var bool
     */
    protected $taxFree;

    /**
     * @var bool
     */
    protected $taxFreeForVatId;

    /**
     * @var bool
     */
    protected $vatIdCheck;

    /**
     * @var bool
     */
    protected $displayStateSelection;

    /**
     * @var bool
     */
    protected $requiresStateSelection;

    /**
     * @var bool
     */
    protected $allowShipping;

    /**
     * @var State[] indexed by id
     */
    protected $states;

    /**
     * @var int
     */
    private $areaId;

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
     * @param bool $taxFree
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;
    }

    /**
     * @param bool $taxFreeForVatId
     */
    public function setTaxFreeForVatId($taxFreeForVatId)
    {
        $this->taxFreeForVatId = $taxFreeForVatId;
    }

    /**
     * @param bool $vatIdCheck
     */
    public function setVatIdCheck($vatIdCheck)
    {
        $this->vatIdCheck = $vatIdCheck;
    }

    /**
     * @param bool $displayStateSelection
     */
    public function setDisplayStateSelection($displayStateSelection)
    {
        $this->displayStateSelection = $displayStateSelection;
    }

    /**
     * @param bool $requiresStateSelection
     */
    public function setRequiresStateSelection($requiresStateSelection)
    {
        $this->requiresStateSelection = $requiresStateSelection;
    }

    /**
     * @param bool $allowShipping
     */
    public function setAllowShipping($allowShipping)
    {
        $this->allowShipping = (bool) $allowShipping;
    }

    /**
     * @return bool
     */
    public function isTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @return bool
     */
    public function isTaxFreeForVatId()
    {
        return $this->taxFreeForVatId;
    }

    /**
     * @return bool
     */
    public function checkVatId()
    {
        return $this->vatIdCheck;
    }

    /**
     * @return bool
     */
    public function displayStateSelection()
    {
        return $this->displayStateSelection;
    }

    /**
     * @return bool
     */
    public function requiresStateSelection()
    {
        return $this->requiresStateSelection;
    }

    /**
     * @return bool
     */
    public function allowShipping()
    {
        return $this->allowShipping;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return Country\State[]
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param Country\State[] $states
     */
    public function setStates($states)
    {
        $this->states = $states;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getAreaId(): ?int
    {
        return $this->areaId;
    }

    public function setAreaId(int $areaId): void
    {
        $this->areaId = $areaId;
    }
}
