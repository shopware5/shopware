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

namespace Shopware\Models\Price;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware Price Model
 *
 * @ORM\Table(name="s_core_pricegroups_discounts")
 * @ORM\Entity
 */
class Discount extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Group
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Price\Group", inversedBy="discounts")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     */
    private $group;

    /**
     * @var \Shopware\Models\Customer\Group
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customergroupID", referencedColumnName="id")
     */
    private $customerGroup;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=false)
     */
    private $discount;

    /**
     * @var float
     *
     * @ORM\Column(name="discountstart", type="float", nullable=false)
     */
    private $start;

    /**
     * @var int
     * @ORM\Column(name="customergroupID", type="integer", nullable=true)
     */
    private $customerGroupId = null;

    /**
     * @var int
     * @ORM\Column(name="groupID", type="integer", nullable=true)
     */
    private $groupId = null;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set group
     *
     * @param Group $group
     *
     * @return discount
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set customerGroup
     *
     * @param $customerGroup
     *
     * @return Discount
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * Get customerGroup
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * Set discount
     *
     * @param float $discount
     *
     * @return discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set start
     *
     * @param float $start
     *
     * @return discount
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return float
     */
    public function getStart()
    {
        return $this->start;
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
