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
 * Shopware\Models\Attribute\PropertyGroup
 *
 * @ORM\Table(name="s_filter_attributes")
 * @ORM\Entity
 */
class PropertyGroup extends ModelEntity
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
     * @var integer $propertyGroupId
     *
     * @ORM\Column(name="filterID", type="integer", nullable=true)
     */
    private $propertyGroupId = null;

    /**
     * @var Shopware\Models\Property\Group
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Property\Group", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="filterID", referencedColumnName="id")
     * })
     */
    private $propertyGroup;

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
     * Set propertyGroup
     *
     * @param Shopware\Models\Property\Group $propertyGroup
     * @return PropertyGroup
     */
    public function setPropertyGroup(\Shopware\Models\Property\Group $propertyGroup = null)
    {
        $this->propertyGroup = $propertyGroup;
        return $this;
    }

    /**
     * Get propertyGroup
     *
     * @return Shopware\Models\Property\Group
     */
    public function getPropertyGroup()
    {
        return $this->propertyGroup;
    }

    /**
     * Set propertyGroupId
     *
     * @param integer $propertyGroupId
     * @return PropertyGroup
     */
    public function setPropertyGroupId($propertyGroupId)
    {
        $this->propertyGroupId = $propertyGroupId;
        return $this;
    }

    /**
     * Get propertyGroupId
     *
     * @return integer
     */
    public function getPropertyGroupId()
    {
        return $this->propertyGroupId;
    }
}
