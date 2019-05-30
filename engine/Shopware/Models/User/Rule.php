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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

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
     * @var \Shopware\Models\User\Role
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Role", inversedBy="rules", fetch="EAGER")
     * @ORM\JoinColumn(name="roleID", referencedColumnName="id")
     */
    private $role;

    /**
     * The resource property is the owning side of the association between rule and permission.
     *
     * @var \Shopware\Models\User\Resource
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Resource", fetch="EAGER")
     * @ORM\JoinColumn(name="resourceID", referencedColumnName="id", nullable=true)
     */
    private $resource;

    /**
     * The privilege property is the owning side of the association between rule and permission.
     *
     * @var \Shopware\Models\User\Privilege
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\User\Privilege", fetch="EAGER")
     * @ORM\JoinColumn(name="privilegeID", referencedColumnName="id", nullable=true)
     */
    private $privilege;

    /**
     * @ORM\Column(name="privilegeID", type="integer", nullable=true)
     *
     * @var int
     */
    private $privilegeId;

    /**
     * @ORM\Column(name="roleID", type="integer", nullable=true)
     *
     * @var int
     */
    private $roleId;

    /**
     * @ORM\Column(name="resourceID", type="integer", nullable=true)
     *
     * @var int
     */
    private $resourceId;

    /**
     * Returns the instance of the Shopware\Models\User\Role model which
     * contains all data about the assigned role.
     *
     * @return \Shopware\Models\User\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Resource model which
     * contains all data about the assigned resource.
     *
     * @return \Shopware\Models\User\Resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Privilege model which
     * contains all data about the assigned privilege.
     *
     * @return \Shopware\Models\User\Privilege|null
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * @return int
     */
    public function getPrivilegeId()
    {
        return $this->privilegeId;
    }

    /**
     * @param int $privilegeId
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
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * @param \Shopware\Models\User\Role $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @param \Shopware\Models\User\Resource $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param \Shopware\Models\User\Privilege|null $privilege
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
