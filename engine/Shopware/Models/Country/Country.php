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
use Shopware\Models\Payment\Payment;

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
     * @var Area|null
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
     * @var ArrayCollection<State>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Country\State", mappedBy="country", orphanRemoval=true, cascade={"persist"})
     */
    protected $states;

    /**
     * INVERSE SIDE
     *
     * @var CountryAttribute|null
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
     * @var string|null
     *
     * @ORM\Column(name="countryname", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="countryiso", type="string", length=255, nullable=true)
     */
    private $iso;

    /**
     * @var string|null
     *
     * @ORM\Column(name="countryen", type="string", length=70, nullable=true)
     */
    private $isoName;

    /**
     * @var int|null
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var string|null
     *
     * @ORM\Column(name="notice", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="taxfree", type="boolean", nullable=true)
     */
    private $taxFree;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="taxfree_ustid", type="boolean", nullable=true)
     */
    private $taxFreeUstId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="taxfree_ustid_checked", type="boolean", nullable=true)
     */
    private $taxFreeUstIdChecked;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var string|null
     *
     * @ORM\Column(name="iso3", type="string", length=3, nullable=true)
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
     * @var ArrayCollection<Payment>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Payment\Payment", mappedBy="countries")
     * @ORM\JoinTable(name="s_core_paymentmeans_countries",
     *     joinColumns={@ORM\JoinColumn(name="countryID", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="paymentID", referencedColumnName="id", nullable=false)}
     * )
     */
    private $payments;

    /**
     * @var int|null
     *
     * @ORM\Column(name="areaID", type="integer", nullable=true)
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
     * @param string|null $name
     *
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $iso
     *
     * @return Country
     */
    public function setIso($iso)
    {
        $this->iso = $iso;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * @param string|null $isoName
     *
     * @return Country
     */
    public function setIsoName($isoName)
    {
        $this->isoName = $isoName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIsoName()
    {
        return $this->isoName;
    }

    /**
     * @param int|null $position
     *
     * @return Country
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string|null $description
     *
     * @return Country
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param bool|null $taxFree
     *
     * @return Country
     */
    public function setTaxFree($taxFree)
    {
        $this->taxFree = $taxFree;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @param bool|null $taxFreeUstId
     *
     * @return Country
     */
    public function setTaxFreeUstId($taxFreeUstId)
    {
        $this->taxFreeUstId = $taxFreeUstId;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTaxFreeUstId()
    {
        return $this->taxFreeUstId;
    }

    /**
     * @param bool|null $taxFreeUstIdChecked
     *
     * @return Country
     */
    public function setTaxFreeUstIdChecked($taxFreeUstIdChecked)
    {
        $this->taxFreeUstIdChecked = $taxFreeUstIdChecked;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTaxFreeUstIdChecked()
    {
        return $this->taxFreeUstIdChecked;
    }

    /**
     * @param bool|null $active
     *
     * @return Country
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string|null $iso3
     *
     * @return Country
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * @return CountryAttribute|null
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
     * @return ArrayCollection<State>
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param ArrayCollection<int, State>|State[]|null $states
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
     * @param Area|null $area
     *
     * @return Country
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return ArrayCollection<Payment>
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param ArrayCollection<Payment> $payments
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
