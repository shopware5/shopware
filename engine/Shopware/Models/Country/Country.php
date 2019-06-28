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

namespace Shopware\Models\Country;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Country as CountryAttribute;

/**
 * Shopware country model represents a single country.
 * <br>
 * The Shopware country model represents a row of the s_core_countries table.
 * One country has the follows associations:
 * <code>
 *
 * </code>
 * The s_core_countries table has the follows indices:
 * <code>

 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_core_countries")
 * @ORM\HasLifecycleCallbacks()
 */
class Country extends ModelEntity
{
    /**
     * OWNING SIDE
     * The area property is the owning side of the association between area and countries.
     * The association is joined over the area id field and the areaID field of the country.
     *
     * @var Area
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Country\Area", inversedBy="countries")
     * @ORM\JoinColumn(name="areaID", referencedColumnName="id")
     */
    protected $area;

    /**
     * INVERSE SIDE
     * The countries property is the inverse side of the association between area and countries.
     * The association is joined over the area id field and the areaID field of the country.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Country\State", mappedBy="country", orphanRemoval=true, cascade={"persist"})
     */
    protected $states;

    /**
     * INVERSE SIDE
     *
     * @var CountryAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Country", mappedBy="country", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="countryname", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="countryiso", type="string", length=255, nullable=false)
     */
    private $iso;

    /**
     * @var string
     *
     * @ORM\Column(name="countryen", type="string", length=70, nullable=false)
     */
    private $isoName;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="notice", type="text", nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="taxfree", type="integer", nullable=false)
     */
    private $taxFree;

    /**
     * @var int
     *
     * @ORM\Column(name="taxfree_ustid", type="integer", nullable=false)
     */
    private $taxFreeUstId;

    /**
     * @var int
     *
     * @ORM\Column(name="taxfree_ustid_checked", type="integer", nullable=false)
     */
    private $taxFreeUstIdChecked;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="iso3", type="string", length=3, nullable=false)
     */
    private $iso3;

    /**
     * @var bool
     *
     * @ORM\Column(name="display_state_in_registration", type="boolean", nullable=false)
     */
    private $displayStateInRegistration = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="force_state_in_registration", type="boolean", nullable=false)
     */
    private $forceStateInRegistration = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="allow_shipping", type="boolean", nullable=false)
     */
    private $allowShipping = true;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Payment\Payment", mappedBy="countries")
     * @ORM\JoinTable(name="s_core_paymentmeans_countries",
     *     joinColumns={@ORM\JoinColumn(name="countryID", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="paymentID", referencedColumnName="id")}
     * )
     */
    private $payments;

    /**
     * @var int
     *
     * @ORM\Column(name="areaID", type="integer", nullable=false)
     */
    private $areaId;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->states = new ArrayCollection();
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
     *
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $iso
     *
     * @return Country
     */
    public function setIso($iso)
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @param string $isoName
     *
     * @return Country
     */
    public function setIsoName($isoName)
    {
        $this->isoName = $isoName;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsoName()
    {
        return $this->isoName;
    }

    /**
     * @param int $position
     *
     * @return Country
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $description
     *
     * @return Country
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $taxFree
     *
     * @return Country
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @param int $taxFreeUstId
     *
     * @return Country
     */
    public function setTaxFreeUstId($taxFreeUstId)
    {
        $this->taxFreeUstId = $taxFreeUstId;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaxFreeUstId()
    {
        return $this->taxFreeUstId;
    }

    /**
     * @param int $taxFreeUstIdChecked
     *
     * @return Country
     */
    public function setTaxFreeUstIdChecked($taxFreeUstIdChecked)
    {
        $this->taxFreeUstIdChecked = $taxFreeUstIdChecked;

        return $this;
    }

    /**
     * @return int
     */
    public function getTaxFreeUstIdChecked()
    {
        return $this->taxFreeUstIdChecked;
    }

    /**
     * @param bool $active
     *
     * @return Country
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $iso3
     *
     * @return Country
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    /**
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @return CountryAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param CountryAttribute|array|null $attribute
     *
     * @return Country
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, CountryAttribute::class, 'attribute', 'country');
    }

    /**
     * @return ArrayCollection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param State[]|null $states
     *
     * @return Country
     */
    public function setStates($states)
    {
        return $this->setOneToMany($states, State::class, 'states', 'country');
    }

    /**
     * OWNING SIDE
     * of the association between countries and area
     *
     * @return Area|null
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param Area|array|null $area
     *
     * @return Country
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param ArrayCollection $payments
     *
     * @return Country
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * @param bool $displayStateInRegistration
     */
    public function setDisplayStateInRegistration($displayStateInRegistration)
    {
        $this->displayStateInRegistration = $displayStateInRegistration;
    }

    /**
     * @return bool
     */
    public function getDisplayStateInRegistration()
    {
        return $this->displayStateInRegistration;
    }

    /**
     * @param bool $forceStateInRegistration
     */
    public function setForceStateInRegistration($forceStateInRegistration)
    {
        $this->forceStateInRegistration = $forceStateInRegistration;
    }

    /**
     * @return bool
     */
    public function getForceStateInRegistration()
    {
        return $this->forceStateInRegistration;
    }

    /**
     * @param bool $allowShipping
     */
    public function setAllowShipping($allowShipping)
    {
        $this->allowShipping = $allowShipping;
    }

    /**
     * @return bool
     */
    public function getAllowShipping()
    {
        return $this->allowShipping;
    }
}
