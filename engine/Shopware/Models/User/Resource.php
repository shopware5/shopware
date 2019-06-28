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
 * Shopware resource model represents a single authentication resource.
 * <br>
 * The Shopware resource model represents a row of the s_user table.
 * One authentication resource has the follows associations:
 * <code>
 *   - Privileges   =>  Shopware\Models\User\Privilege  [1:n] [s_core_acl_privileges]
 * </code>
 * The core_acl_resources table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Table(name="s_core_acl_resources")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Resource extends ModelEntity implements \Zend_Acl_Resource_Interface
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * The privileges property is the inverse side of the association between resource and privileges.
     * The association is joined over the s_core_acl_privileges.resourceID field and the s_core_acl_resources.id
     *
     * @var ArrayCollection<\Shopware\Models\User\Privilege>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\User\Privilege", mappedBy="resource")
     */
    private $privileges;

    /**
     * Initials the privileges collection
     */
    public function __construct()
    {
        $this->privileges = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return \Shopware\Models\User\Resource
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
     * Getter function for the pluginId property
     *
     * @return int
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * Setter function for the pluginId property
     *
     * @param int|null $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * Returns an array collection of Shopware\Models\User\Privilege model instances, which
     * contains all assigned resource privileges. The association is defined over
     * the Resource.privileges property (INVERSE SIDE) and the Privilege.resource (OWNING SIDE) property.
     * The privilege data is joined over the s_core_acl_privileges.resourceID field.
     *
     * @return ArrayCollection<Privilege>
     */
    public function getPrivileges()
    {
        return $this->privileges;
    }

    /**
     * Setter function for the privileges association property which contains many instances of the Shopware\Models\User\Privileges model.
     * The ArrayCollection contains all assigned resource privileges. The association is defined over
     * the Resource.privileges property (INVERSE SIDE) and the Privilege.resource (OWNING SIDE) property.
     * The privilege data is joined over the s_core_acl_privileges.resourceID field.
     *
     * @param ArrayCollection<Privilege> $privileges
     */
    public function setPrivileges($privileges)
    {
        $this->privileges = $privileges;
    }

    /**
     * Event listener method which fired before the model will be removed (DELETE)
     * over Shopware()->Models()->persist() / ->flush().
     *
     * Removes the released resource.
     *
     * @ORM\PreRemove()
     */
    public function onRemove()
    {
        $sql = 'DELETE FROM s_core_acl_roles WHERE resourceID = ?';
        Shopware()->Db()->query($sql, [$this->id]);
    }

    /**
     * Returns the string identifier of the Resource
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->name;
    }
}
