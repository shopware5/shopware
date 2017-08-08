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

namespace Shopware\Country\Struct;

use Shopware\CountryArea\Struct\CountryArea;
use Shopware\Framework\Struct\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Country extends Struct
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
    protected $shippingFree;

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
     * @var \Shopware\CountryState\Struct\CountryState[] indexed by id
     */
    protected $states;

    /**
     * @var CountryArea|null
     */
    protected $area;

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
    public function getId(): int
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIso(): string
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
    public function getEn(): string
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
    public function getDescription(): string
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
    public function getIso3(): string
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
     * @param bool $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
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
     * @return bool
     */
    public function isShippingFree(): bool
    {
        return $this->shippingFree;
    }

    /**
     * @return bool
     */
    public function isTaxFree(): bool
    {
        return $this->taxFree;
    }

    /**
     * @return bool
     */
    public function isTaxFreeForVatId(): bool
    {
        return $this->taxFreeForVatId;
    }

    /**
     * @return bool
     */
    public function checkVatId(): bool
    {
        return $this->vatIdCheck;
    }

    /**
     * @return bool
     */
    public function displayStateSelection(): bool
    {
        return $this->displayStateSelection;
    }

    /**
     * @return bool
     */
    public function requiresStateSelection(): bool
    {
        return $this->requiresStateSelection;
    }

    /**
     * @return \Shopware\CountryState\Struct\CountryState[]
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param \Shopware\CountryState\Struct\CountryState[] $states
     */
    public function setStates($states)
    {
        $this->states = $states;
    }

    /**
     * @return int
     */
    public function getPosition(): int
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
    public function isActive(): bool
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

    public function getArea(): ?CountryArea
    {
        return $this->area;
    }

    public function setArea(?CountryArea $area): void
    {
        $this->area = $area;
    }
}
