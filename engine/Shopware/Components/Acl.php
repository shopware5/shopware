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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\User\Privilege;
use Shopware\Models\User\Resource;
use Shopware\Models\User\Role;
use Shopware\Models\User\Rule;

/**
 * Shopware ACL Components
 */
class Shopware_Components_Acl extends Zend_Acl
{
    /**
     * @var ModelManager
     */
    private $em;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
        $this->initShopwareAclTree();
    }

    /**
     * Get all resources from database and add to acl tree
     *
     * @return \Shopware_Components_Acl
     */
    public function initAclResources()
    {
        $repository = $this->em->getRepository(Resource::class);
        $resources = $repository->findAll();

        /** @var Shopware\Models\User\Resource $resource */
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }

        return $this;
    }

    /**
     * Get all roles from s_core_auth_roles - check for parent role and add to tree
     *
     * @return \Shopware_Components_Acl
     */
    public function initAclRoles()
    {
        $repository = $this->em->getRepository(Role::class);
        $roles = $repository->findAll();

        /** @var \Shopware\Models\User\Role $role */
        foreach ($roles as $role) {
            /** @var \Shopware\Models\User\Role|null $parent */
            $parent = $role->getParent();

            // Parent exist and not already added?
            if ($parent && !$this->hasRole($parent)) {
                // Register role and register role privileges
                $this->addRole($parent);
            }

            // If parent exists, the pass the parent name to the addRole function
            $this->addRole($role, $parent);
        }

        return $this;
    }

    /**
     * Resolve all resources / privileges / role dependencies from database
     * Should work as followed:
     * If resourceID & privilegeID = null -> Global admin
     * If resourceID != null AND privilegeID = null -> Module admin
     * If resourceID & privilegeID is set - Grant permission to a certain privilege of a particular module
     *
     * @return \Shopware_Components_Acl
     */
    public function initAclRoleConditions()
    {
        $rules = $this->em->getRepository(Rule::class)->findAll();

        /** @var \Shopware\Models\User\Rule $rule */
        foreach ($rules as $rule) {
            $role = $rule->getRole();

            $resource = $rule->getResource();
            $privilege = $rule->getPrivilege();

            if ($resource === null && $privilege === null) {
                $this->allow($role);
            } elseif ($privilege === null) {
                $this->allow($role, $resource);
            } else {
                $this->allow($role, $resource, $privilege->getName());
            }
        }

        return $this;
    }

    /**
     * Is the resource identified by $resourceName already in database ?
     *
     * @param string $resourceName
     *
     * @return bool
     */
    public function hasResourceInDatabase($resourceName)
    {
        $repository = $this->em->getRepository(Resource::class);
        $resource = $repository->findOneBy(['name' => $resourceName]);

        return !empty($resource);
    }

    /**
     * Create a new resource and optionally privileges, menu item relationships and plugin dependency
     *
     * @param string     $resourceName - unique identifier or resource key
     * @param array|null $privileges   - optionally array [a,b,c] of new privileges
     * @param null       $menuItemName - optionally s_core_menu.name item to link to this resource
     * @param null       $pluginID     - optionally pluginID that implements this resource
     *
     * @throws Enlight_Exception
     */
    public function createResource($resourceName, array $privileges = null, $menuItemName = null, $pluginID = null)
    {
        // Check if resource already exists
        if ($this->hasResourceInDatabase($resourceName)) {
            throw new Enlight_Exception(sprintf('Resource "%s" already exists', $resourceName));
        }

        $resource = new Resource();
        $resource->setName($resourceName);
        $resource->setPluginId($pluginID);

        if (!empty($privileges)) {
            $privilegeObjects = [];

            foreach ($privileges as $name) {
                $privilege = new Privilege();
                $privilege->setName($name);
                $privilege->setResource($resource);

                $this->em->persist($privilege);

                $privilegeObjects[] = $privilege;
            }

            $resource->setPrivileges(new ArrayCollection($privilegeObjects));
        }

        $this->em->persist($resource);
        $this->em->flush();
    }

    /**
     * Create a privilege in a particular resource
     *
     * @param int    $resourceId
     * @param string $name
     */
    public function createPrivilege($resourceId, $name)
    {
        $privilege = new Privilege();
        $privilege->setName($name);
        $privilege->setResourceId($resourceId);

        $this->em->persist($privilege);
        $this->em->flush();
    }

    /**
     * Delete resource and its privileges from database
     *
     * @param string $resourceName
     *
     * @return bool
     */
    public function deleteResource($resourceName)
    {
        $repository = $this->em->getRepository(Resource::class);

        /** @var \Shopware\Models\User\Resource|null $resource */
        $resource = $repository->findOneBy(['name' => $resourceName]);
        if ($resource === null) {
            return false;
        }

        // The mapping table s_core_acl_roles must be cleared manually.
        $this->em->getConnection()->executeUpdate(
            'DELETE FROM s_core_acl_roles WHERE resourceID = ?',
            [$resource->getId()]
        );

        foreach ($resource->getPrivileges() as $privilege) {
            $this->em->remove($privilege);
        }
        // The privileges will be removed automatically
        $this->em->remove($resource);
        $this->em->flush();

        return true;
    }

    /**
     * Create Shopware acl tree
     */
    private function initShopwareAclTree()
    {
        $this->initAclResources()
            ->initAclRoles()
            ->initAclRoleConditions();
    }
}
