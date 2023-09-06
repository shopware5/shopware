<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\User;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\User\Resource as UserResource;

/**
 * Shopware rule model represents a acl rule in shopware.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_acl_roles")
 * @ORM\HasLifecycleCallbacks()
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
     * The role property is the owning side of the association between rule and permission.
     *
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Role", inversedBy="rules", fetch="EAGER")
     * @ORM\JoinColumn(name="roleID", referencedColumnName="id", nullable=false)
     */
    private $role;

    /**
     * The resource property is the owning side of the association between rule and permission.
     *
     * @var UserResource|null
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Resource", fetch="EAGER")
     * @ORM\JoinColumn(name="resourceID", referencedColumnName="id", nullable=true)
     */
    private $resource;

    /**
     * The privilege property is the owning side of the association between rule and permission.
     *
     * @var Privilege|null
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\User\Privilege", fetch="EAGER")
     * @ORM\JoinColumn(name="privilegeID", referencedColumnName="id", nullable=true)
     */
    private $privilege;

    /**
     * @ORM\Column(name="privilegeID", type="integer", nullable=true)
     *
     * @var int|null
     */
    private $privilegeId;

    /**
     * @ORM\Column(name="roleID", type="integer", nullable=false)
     *
     * @var int
     */
    private $roleId;

    /**
     * @ORM\Column(name="resourceID", type="integer", nullable=true)
     *
     * @var int|null
     */
    private $resourceId;

    /**
     * Returns the instance of the Shopware\Models\User\Role model which
     * contains all data about the assigned role.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Resource model which
     * contains all data about the assigned resource.
     *
     * @return UserResource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Privilege model which
     * contains all data about the assigned privilege.
     *
     * @return Privilege|null
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * @return int|null
     */
    public function getPrivilegeId()
    {
        return $this->privilegeId;
    }

    /**
     * @param int|null $privilegeId
     */
    public function setPrivilegeId($privilegeId)
    {
        $this->privilegeId = $privilegeId;
    }

    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * @param int $roleId
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
     * @return int|null
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param int|null $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @param Role $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @param UserResource|null $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param Privilege|null $privilege
     */
    public function setPrivilege($privilege)
    {
        $this->privilege = $privilege;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
