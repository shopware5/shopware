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

namespace Shopware\Models\Tax;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * The Shopware Model represents the Taxes.
 * <br>
 * Tax codes and there percentages
 *
 * Relations and Associations
 * <code>
 *
 * </code>
 * The s_media_album table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_tax_rules")
 */
class Rule extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="tax", type="float", nullable=false)
     */
    private $tax = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = false;

    /**
     * @var int
     *
     * @ORM\Column(name="areaID", type="integer", nullable=true)
     */
    private $areaId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="countryID", type="integer", nullable=true)
     */
    private $countryId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="stateID", type="integer", nullable=true)
     */
    private $stateId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="groupID", type="integer", nullable=false)
     */
    private $groupId;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_groupID", type="integer", nullable=false)
     */
    private $customerGroupId;

    /**
     * The area property is the owning side of the association between tax rule and area.
     * The association is joined over the tax rule areaID field and the id field of the area.
     *
     * @var \Shopware\Models\Country\Area
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Country\Area")
     * @ORM\JoinColumn(name="areaID", referencedColumnName="id")
     */
    private $area;

    /**
     * The country property is the owning side of the association between tax rule and country.
     * The association is joined over the tax rule countryID field and the id field of the country.
     *
     * @var \Shopware\Models\Country\Country
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Country\Country")
     * @ORM\JoinColumn(name="countryID", referencedColumnName="id")
     */
    private $country;

    /**
     * The state property is the owning side of the association between tax rule and state.
     * The association is joined over the tax rule stateID field and the id field of the state.
     *
     * @var \Shopware\Models\Country\State
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Country\State")
     * @ORM\JoinColumn(name="stateID", referencedColumnName="id")
     */
    private $state;

    /**
     * The group property is the owning side of the association between tax rule and group.
     * The association is joined over the tax rule groupID field and the id field of the group.
     *
     * @var \Shopware\Models\Tax\Tax
     *
     * @ORM\ManyToOne(targetEntity="Tax", inversedBy="rules")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     */
    private $group;

    /**
     * @var \Shopware\Models\Customer\Group
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_groupID", referencedColumnName="id")
     */
    private $customerGroup;

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
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param \Shopware\Models\Country\Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return \Shopware\Models\Country\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param int $areaId
     */
    public function setAreaId($areaId)
    {
        $this->areaId = $areaId;
    }

    /**
     * @return int
     */
    public function getAreaId()
    {
        return $this->areaId;
    }

    /**
     * @param \Shopware\Models\Country\Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return \Shopware\Models\Country\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }

    /**
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param \Shopware\Models\Tax\Tax $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return \Shopware\Models\Tax\Tax
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param \Shopware\Models\Country\State $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return \Shopware\Models\Country\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $stateId
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;
    }

    /**
     * @return int
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Models\Customer\Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @param int $customerGroupId
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;
    }
}
