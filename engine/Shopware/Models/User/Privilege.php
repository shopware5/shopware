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

namespace Shopware\Models\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware privilege model represents a single authentication privilege.
 * <br>
 * The Shopware privilege model represents a row of the core_acl_privileges table.
 * One authentication privilege has the follows associations:
 * <code>
 *   - Resource   =>  Shopware\Models\User\Resource  [n:1 [s_core_acl_resources]
 * </code>
 * The core_acl_privileges table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`, `resourceID`)
 * </code>
 *
 * @ORM\Table(name="s_core_acl_privileges")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Privilege extends ModelEntity
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
     * @var int
     *
     * @ORM\Column(name="resourceID", type="integer", nullable=false)
     */
    private $resourceId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * The resource property is the owning side of the association between resource and privileges.
     * The association is joined over the s_core_acl_privileges.resourceID field and the s_core_acl_resources.id
     *
     * @var \Shopware\Models\User\Resource
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\User\Resource", inversedBy="privileges")
     * @ORM\JoinColumn(name="resourceID", referencedColumnName="id")
     */
    private $resource;

    /**
     * @ORM\ManyToMany(targetEntity="Shopware\Models\User\Privilege")
     * @ORM\JoinTable(name="s_core_acl_privilege_requirements",
     *     joinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="required_privilege_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection<Privilege>
     */
    private $requirements;

    public function __construct()
    {
        $this->requirements = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Resource model which
     * contains all data about the assigned privilege resource. The association is defined over
     * the Privilege.resource property (OWNING SIDE) and the Resource.privileges (INVERSE SIDE) property.
     * The resource data is joined over the s_core_acl_privileges.resourceID field.
     *
     * @return \Shopware\Models\User\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Setter function for the resource property which is an instance of the Shopware\Models\User\Resource model, which
     * contains all data about the assigned privilege resource. The association is defined over
     * the Privilege.resource property (OWNING SIDE) and the Resource.privileges (INVERSE SIDE) property.
     * The resource data is joined over the s_core_acl_privileges.resourceID field.
     *
     * @param \Shopware\Models\User\Resource $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param int|null $resourceId
     *
     * @return Privilege
     */
    public function setResourceId($resourceId)
    {
        if (!empty($resourceId)) {
            /** @var \Shopware\Models\User\Resource resource */
            $resource = Shopware()->Models()->find(\Shopware\Models\User\Resource::class, $resourceId);
            $this->resource = $resource;
        }
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param string $name
     *
     * @return Privilege
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
     * @return ArrayCollection<Privilege>
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setRequirements(ArrayCollection $requirements)
    {
        $this->requirements = $requirements;
    }

    /**
     * Event listener method which fired before the model will be removed (DELETE)
     * over Shopware()->Models()->persist() / ->flush().
     *
     * Removes the released privileges.
     *
     * @ORM\PreRemove()
     */
    public function onRemove()
    {
        $sql = 'DELETE FROM s_core_acl_roles WHERE resourceID = ? AND privilegeID = ?';
        Shopware()->Db()->query($sql, [$this->resourceId, $this->id]);
    }
}
