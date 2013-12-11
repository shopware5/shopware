<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Shopware ACL Components
 */
class Shopware_Components_Acl extends Zend_Acl {

    /**
     * @var Enlight_Db
     */
    protected $databaseObject;

    /**
     * Create shopware acl tree
     * @param $databaseObject
     */
    public function initShopwareAclTree($databaseObject)
    {
        $this->databaseObject = $databaseObject;

        $this->initAclResources()
             ->initAclRoles()
             ->initAclRoleConditions();

    }

    /**
     * Get all resources from database and add to acl tree
     *
     * @return \Shopware_Components_Acl
     */
    public function initAclResources ()
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\User\Resource');
        $resources = $repository->findAll();

        /**@var $resource Shopware\Models\User\Resource */
        foreach($resources as $resource) {
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
        $repository = Shopware()->Models()->getRepository('Shopware\Models\User\Role');
        $roles = $repository->findAll();

        /** @var $role \Shopware\Models\User\Role */
        foreach ($roles as $role) {
            /** @var $parent \Shopware\Models\User\Role */
            $parent = $role->getParent();

            //parent exist and not already added?
            if ($parent && !$this->hasRole($parent)) {
                //register role and register role privileges
                $this->addRole($parent);
            }

            //if parent exists, the pass the parent name to the addRole function
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
        $rules = Shopware()->Models()->getRepository('Shopware\Models\User\Rule')->findAll();

        /** @var $rule \Shopware\Models\User\Rule */
        foreach ($rules as $rule) {
            $role = $rule->getRole();

            $resource = $rule->getResource();
            $privilege = $rule->getPrivilege();

            if ($resource === NULL && $privilege === NULL) {
                $this->allow($role);
            } elseif ($privilege === NULL) {
                $this->allow($role, $resource);
            } else {
                $this->allow($role, $resource, $privilege->getName());
            }
        }

        return $this;
    }

    /**
     * Is the resource identified by $resourceName already in database ?
     * @param $resourceName
     * @return bool
     */
    public function hasResourceInDatabase($resourceName)
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\User\Resource');
        $resource = $repository->findOneBy(array("name" => $resourceName));

        return !empty($resource);
    }

    /**
     * Create a new resource and optionally privileges, menu item relationships and plugin dependency
     * @param $resourceName - unique identifier or resource key
     * @param array|null $privileges - optionally array [a,b,c] of new privileges
     * @param null $menuItemName - optionally s_core_menu.name item to link to this resource
     * @param null $pluginID - optionally pluginID that implements this resource
     * @throws Enlight_Exception
     */
    public function createResource($resourceName,array $privileges = null,$menuItemName = null, $pluginID = null)
    {
        // Check if resource already exists
        if (!$this->hasResourceInDatabase($resourceName)) {

            $resource = new \Shopware\Models\User\Resource();
            $resource->setName($resourceName);
            $resource->setPluginId($pluginID);

            Shopware()->Models()->persist($resource);
            Shopware()->Models()->flush();

            if (!empty($privileges)) {
                foreach ($privileges as $privilege) {
                    $this->createPrivilege($resource->getId(), $privilege);
                }
            }
            if (!empty($menuItemName)) {
                $this->databaseObject->query("UPDATE s_core_menu SET resourceID = ? WHERE name = ?",array($resource->getId(),$menuItemName));
            }
        } else {
            throw new Enlight_Exception("Resource $resourceName already exists");
        }
    }

    /**
     * Create a privilege in a particular resource
     * @param int $resourceId
     * @param string $name
     */
    public function createPrivilege($resourceId, $name)
    {
        $privilege = new \Shopware\Models\User\Privilege();
        $privilege->setName($name);
        $privilege->setResourceId($resourceId);
        Shopware()->Models()->persist($privilege);
        Shopware()->Models()->flush();
    }

    /**
     * Delete resource and its privileges from database
     * @param $resourceName
     * @return bool
     */
    public function deleteResource($resourceName)
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\User\Resource');
        /** @var $resource \Shopware\Models\User\Resource */
        $resource = $repository->findOneBy(array("name" => $resourceName));
        if (empty($resource)) {
            return false;
        }

        //remove the resource flag in the s_core_menu manually.
        $this->databaseObject->query("UPDATE s_core_menu SET resourceID = ?",array($resource->getId()));

        //The mapping table s_core_acl_roles must be cleared manually.
        $this->databaseObject->query("DELETE FROM s_core_acl_roles WHERE resourceID = ?",array($resource->getId()));

        //The privileges will be removed automatically
        Shopware()->Models()->remove($resource);
        Shopware()->Models()->flush();
        return true;
    }

    /**
     * Make a dbdeploy style sql export for a certain resource
     * @param $resourceName string - identify resource to export
     * @throws Enlight_Exception
     */
    public function exportResourceSQL($resourceName)
    {
        if (!$this->hasResourceInDatabase($resourceName)){
           throw new Enlight_Exception("Resource $resourceName do not exists in database");
        }
        $privilegeInsert = ""; $menuUpdate = "";
        $resourceInsert = "INSERT INTO s_core_acl_resources (name) VALUES ('".$resourceName."');\n";
        $fetchPrivileges = $this->databaseObject->fetchAll("SELECT * FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = ?)",array($resourceName));
        foreach ($fetchPrivileges as $privilege){
            $privilegeInsert .= "\nINSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = '$resourceName'), '{$privilege["name"]}'); ";
        }

        $getMenuItem = $this->databaseObject->fetchOne("SELECT name FROM s_core_menu WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = ?)",array($resourceName));
        if (!empty($getMenuItem)){
            $menuUpdate = "\nUPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = '$resourceName') WHERE name = '$getMenuItem'";
        }

        header("Content-Type: text/plain");

        echo "-- Add acl resources and privileges for resource $resourceName //\n\n";
        echo $resourceInsert;
        echo $privilegeInsert;
        echo $menuUpdate;
        echo "\n\n-- //@UNDO\n";
        echo "\nDELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = '$resourceName');";
        echo "\nDELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = '$resourceName');";
        if (!empty($getMenuItem)){
        echo "\nUPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = '$resourceName');";
        }
        echo "\nDELETE FROM s_core_acl_resources WHERE name = '$resourceName';";
        echo "\n\n-- //";
        exit;
    }
}
