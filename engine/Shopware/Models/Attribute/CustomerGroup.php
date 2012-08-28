<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\CustomerGroup
 *
 * @ORM\Table(name="s_core_customergroups_attributes")
 * @ORM\Entity
 */
class CustomerGroup extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $customerGroupId
     *
     * @ORM\Column(name="customerGroupID", type="integer", nullable=true)
     */
    private $customerGroupId = null;

    /**
     * @var Shopware\Models\Customer\Group
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customerGroupID", referencedColumnName="id")
     * })
     */
    private $customerGroup;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set customerGroup
     *
     * @param Shopware\Models\Customer\Group $customerGroup
     * @return CustomerGroup
     */
    public function setCustomerGroup(\Shopware\Models\Customer\Group $customerGroup = null)
    {
        $this->customerGroup = $customerGroup;
        return $this;
    }

    /**
     * Get customerGroup
     *
     * @return Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * Set customerGroupId
     *
     * @param integer $customerGroupId
     * @return CustomerGroup
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
    }

    /**
     * Get customerGroupId
     *
     * @return integer
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }
}
