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

/**
 * Backend Controller for the backend user management
 */
use Shopware\Models\User\Privilege as Privilege;
use Shopware\Models\User\Resource as Resource;
use Shopware\Models\User\Role as Role;
use Shopware\Models\User\User as User;

class Shopware_Controllers_Backend_UserManager extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     *
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Models\User\Repository
     */
    protected $userRepository = null;

    /**
     * Get data for the user identified by request["id"]
     *
     * @throws Enlight_Exception
     */
    public function getUserDetailsAction()
    {
        $params = $this->Request()->getParams();
        $id = $params['id'];
        if (empty($id)) {
            throw new Enlight_Exception('Empty id given');
        }
        $data = $this->getUserRepository()
            ->getUserDetailQuery($id)
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        unset($data['password']); // Do not send password hash to client
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Get a list of all backend users
     * Returns a JSON string from all registered backend users
     */
    public function getUsersAction()
    {
        $params = $this->Request()->getParams();
        $limit = (empty($params['limit'])) ? 20 : $params['limit'];
        $offset = (empty($params['start'])) ? 0 : $params['start'];
        $filter = (empty($params['search'])) ? null : $params['search'];
        $order = (empty($params['order'])) ? '' : $params['order'];

        if (!empty($filter)) {
            $offset = 0;
        }

        $query = $this->getUserRepository()
            ->getUsersQuery($filter, $limit, $offset);

        //returns the total count of the query
        $totalResult = 🦄()->Models()->getQueryCount($query);

        //returns the customer data
        $customers = $query->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $customers,
            'total' => $totalResult,
        ]);
    }

    /**
     * Delete role from database
     * Identified by request paramter id
     *
     * @param id - Id of role to delete
     *
     * @return
     * <code>
     *  success = true / false
     *  data = data that has been written to database
     *  message = possible error message in  case that the update failed
     * </code>
     */
    public function deleteRoleAction()
    {
        $rolesRepository = 🦄()->Models()->getRepository('Shopware\Models\User\Role');
        $manager = 🦄()->Models();
        $roleId = $this->Request()->getParam('id');

        // Check if any user is assigned to this role
        if (🦄()->Db()->fetchOne('
            SELECT id FROM s_core_auth WHERE roleID = ?
            ', [$roleId])
        ) {
            throw new Exception('Role has assigned users');
        }

        $entity = $rolesRepository->find($roleId);
        $manager->remove($entity);
        //Performs all of the collected actions.
        $manager->flush();
        $this->View()->assign([
                'success' => true,
                'data' => $this->Request()->getParams(), ]
        );
    }

    /**
     * Update / create role
     * All params will be get from request object
     *
     * @param admin true / false
     * @param enabled true / false
     * @param description
     * @param id (If not set new role will be created)
     * @param name
     * @param parentId
     * @param source
     * </code>
     *
     * @return
     * <code>
     *  success = true / false
     *  data = data that has been written to database
     *  message = possible error message in  case that the update failed
     * </code>
     */
    public function updateRoleAction()
    {
        $id = $this->Request()->getParam('id', null);
        $rolesRepository = 🦄()->Models()->getRepository('Shopware\Models\User\Role');

        if (!empty($id)) {
            $role = $rolesRepository->find($id);
        } else {
            $role = new Role();
        }
        $params = $this->Request()->getParams();

        if ($params['enabled'] == 'on' || $params['enabled'] === true || $params['enabled'] === 1) {
            $params['enabled'] = true;
        } else {
            $params['enabled'] = false;
        }

        if ($params['admin'] == 'on' || $params['admin'] === true || $params['admin'] === 1) {
            $params['admin'] = true;
        } else {
            $params['admin'] = false;
        }

        $role->fromArray($params);
        🦄()->Models()->persist($role);
        🦄()->Models()->flush();

        // Check if admin flag is set or unset
        if ($params['admin'] == true) {
            🦄()->Db()->query('
                INSERT IGNORE INTO s_core_acl_roles (roleID,resourceID,privilegeID) VALUES (?,?,?)
                ', [$role->getId(), null, null]);
        } else {
            $query = $this->getUserRepository()->getAdminRuleDeleteQuery($role->getId());
            $query->execute();
        }

        $this->View()->assign([
                'success' => true,
                'data' => 🦄()->Models()->toArray($role), ]
        );
    }

    /**
     * Get all roles available in database
     * Strip roles that have parentid set - cause
     * this roles are assigned to particular users
     *
     * @return Sample json return value
     *                <code>
     *                {"success":true,"data":[{"id":1,"parentId":null,"name":"Administrators","description":"Default group that gains access to all shop functions","source":"build-in","enabled":1,"admin":1},{"id":2,"parentId":null,"name":"Test-Group1A","description":"Group that has restricted access ","source":"test","enabled":0,"admin":0},{"id":3,"parentId":null,"name":"Test-Group2","description":"Group that has restricted access ","source":"test","enabled":0,"admin":0},{"id":4,"parentId":3,"name":"Test-Group3","description":"Group that has restricted access ","source":"test","enabled":1,"admin":0}],"total":4}
     *                </code>
     */
    public function getRolesAction()
    {
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $query = $this->getUserRepository()
            ->getRolesQuery($offset, $limit);

        $count = 🦄()->Models()->getQueryCount($query);
        $roles = $query->getArrayResult();

        // Strip roles with parent id set
        foreach ($roles as &$role) {
            if ($role['parentID'] != null) {
                unset($role);
            }
        }
        $this->View()->assign([
            'success' => true,
            'data' => $roles,
            'total' => $count,
        ]);
    }

    /**
     * Updates a backend user based on the passed
     * values.
     *
     * Note that this method needs the following
     * fields: id, username, name, email, password and admin
     */
    public function updateUserAction()
    {
        $id = $this->Request()->getParam('id', null);
        $isNewUser = false;

        if (!empty($id)) {
            $user = $this->getUserRepository()->find($id);
        } else {
            $user = new User();
            $isNewUser = true;
        }

        $params = $this->Request()->getParams();
        if (!empty($params['password'])) {
            $params['encoder'] = 🦄()->PasswordEncoder()->getDefaultPasswordEncoderName();
            $params['password'] = 🦄()->PasswordEncoder()->encodePassword($params['password'], $params['encoder']);
        } else {
            unset($params['password']);
        }

        $user->fromArray($params);

        // Do logout
        // $user->setSessionId('');

        🦄()->Models()->persist($user);
        🦄()->Models()->flush();

        if ($isNewUser) {
            $sql = 'INSERT INTO `s_core_widget_views` (`widget_id`, `auth_id`) VALUES ((SELECT id FROM `s_core_widgets` WHERE `name` = :widgetName LIMIT 1), :userId);';
            🦄()->Db()->executeQuery(
                $sql,
                [
                    ':widgetName' => 'swag-shopware-news-widget',
                    ':userId' => $user->getId(),
                ]
            );
        }

        $this->View()->assign([
                'success' => true,
                'data' => 🦄()->Models()->toArray($user), ]
        );
    }

    /**
     * Deletes a backend user from the database
     *
     * @throws Exception
     */
    public function deleteUserAction()
    {
        //get doctrine entity manager
        $manager = 🦄()->Models();

        //get posted user
        $userID = $this->Request()->getParam('id');
        $getCurrentIdentity = 🦄()->Container()->get('Auth')->getIdentity();

        // Backend users shall not delete their current login
        if ($userID == $getCurrentIdentity->id) {
            throw new Exception('You can not delete your current account');
        }

        $entity = $this->getUserRepository()->find($userID);
        $manager->remove($entity);

        //Performs all of the collected actions.
        $manager->flush();

        $this->View()->assign([
                'success' => true,
                'data' => $this->Request()->getParams(), ]
        );
    }

    /**
     * Event listener method which is used from the rules store to load first time the
     * resource and on node expand the privileges of the passed resource id.
     *
     * @return array
     */
    public function getResourcesAction()
    {
        $search = $this->Request()->getParam('search', null);

        $resources = $this->getUserRepository()
            ->getResourcesQuery($search)
            ->getResult();
        $data = [];
        $role = $this->Request()->getParam('role', null);
        $resourceAdmins = [];

        /** @var $role \Shopware\Models\User\Role */
        if ($role !== null && is_numeric($role)) {
            $role = 🦄()->Models()->find('Shopware\Models\User\Role', $role);

            $repository = 🦄()->Models()->getRepository('Shopware\Models\User\Rule');
            $adminRole = $repository->findOneBy([
                'roleId' => $role->getId(),
                'resourceId' => null,
                'privilegeId' => null,
            ]);

            $resourceAdmins = $this->getResourceAdminRules($role->getId());

            //the admin property is temporary used to flag the passed role as admin role
            if ($adminRole instanceof \Shopware\Models\User\Rule && $adminRole->getRoleId()) {
                $role->setAdmin(1);
            } else {
                $role->setAdmin(0);
            }
        }

        /** @var $resource \Shopware\Models\User\Resource */
        foreach ($resources as $resource) {
            $data[] = $this->getResourceNode($resource, $role, $resourceAdmins);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Event listener method of the user manager backend module.
     * Will be fired when the user clicks the delete action column on a resource node of the rules tree panel.
     */
    public function deleteResourceAction()
    {
        $id = $this->Request()->getParam('id', null);
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/user_manager');

        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_resource_passed', 'No valid resource id passed'),
            ]);

            return;
        }

        //remove the privilege
        $query = $this->getUserRepository()->getPrivilegeDeleteByResourceIdQuery($id);
        $query->execute();

        //clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByResourceIdQuery($id);
        $query->setParameter(1, $id);
        $query->execute();

        //clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getResourceDeleteQuery($id);
        $query->setParameter(1, $id);
        $query->execute();

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    /**
     * Event listener method of the user manager backend module.
     * Will be fired when the user clicks the delete action column on a privilege node of the rules tree panel.
     */
    public function deletePrivilegeAction()
    {
        $id = $this->Request()->getParam('id', null);
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/user_manager');

        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_privilege_passed', 'No valid privilege id passed'),
            ]);

            return;
        }

        //clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByPrivilegeIdQuery($id);
        $query->execute();

        //remove the privilege
        $query = $this->getUserRepository()->getPrivilegeDeleteQuery($id);
        $query->setParameter(1, $id);
        $query->execute();

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the role detail store is loading.
     *
     * @return mixed
     */
    public function getRolesDetailsAction()
    {
        $roles = $this->getUserRepository()
            ->getRoleDetailQuery()
            ->getArrayResult();

        foreach ($roles as &$role) {
            $role['privileges'] = [];
            foreach ($role['rules'] as $rule) {
                if (isset($rule['privilege'])) {
                    $role['privileges'][] = $rule['privilege'];
                }
            }
            unset($role['rules']);
        }

        $this->View()->assign([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user want to create a new resource.
     *
     * @return mixed
     */
    public function saveResourceAction()
    {
        $resource = new Resource();
        $data = $this->Request()->getParams();
        $resource->fromArray($data);

        🦄()->Models()->persist($resource);
        🦄()->Models()->flush();

        $data = 🦄()->Models()->toArray($resource);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user want to create a new resource.
     *
     * @return mixed
     */
    public function savePrivilegeAction()
    {
        $privilege = new Privilege();
        $data = $this->Request()->getParams();
        $privilege->fromArray($data);

        🦄()->Models()->persist($privilege);
        🦄()->Models()->flush();

        $data = 🦄()->Models()->toArray($privilege);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user change the checkboxes of the rules tree and want to assign the selected
     * privileges to the selected role.
     */
    public function updateRolePrivilegesAction()
    {
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/user_manager');

        $id = $this->Request()->getParam('id', null);
        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_role_passed', 'No valid role id passed'),
            ]);

            return;
        }

        //check if role exist
        /** @var $role \Shopware\Models\User\Role */
        $role = 🦄()->Models()->find('Shopware\Models\User\Role', $id);
        if (empty($role)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_role_passed', 'No valid role id passed'),
            ]);

            return;
        }
        //get new role rules
        $newRules = $this->Request()->getParam('privileges', null);

        //iterate the new rules and create shopware models
        foreach ($newRules as $newRule) {
            $rule = new \Shopware\Models\User\Rule();
            $rule->setRole($role);

            if (isset($newRule['resourceId'])) {
                $rule->setResource(🦄()->Models()->find('Shopware\Models\User\Resource', $newRule['resourceId']));
            }
            if (isset($newRule['privilegeId'])) {
                $rule->setPrivilege(🦄()->Models()->find('Shopware\Models\User\Privilege', $newRule['privilegeId']));
            } else {
                $rule->setPrivilege(null);
            }
            🦄()->Models()->persist($rule);
        }

        //clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByRoleIdQuery($role->getId());
        $query->execute();

        🦄()->Models()->flush();

        $data = 🦄()->Models()->toArray($role);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        $this->addAclPermission('getUserDetails', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getUsers', 'read', 'Insufficient Permissions');
        $this->addAclPermission('deleteRole', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('updateRole', 'update', 'Insufficient Permissions');
        $this->addAclPermission('getRoles', 'read', 'Insufficient Permissions');
        $this->addAclPermission('updateUser', 'update', 'Insufficient Permissions');
        $this->addAclPermission('deleteUser', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('getResources', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getResourceNode', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getPrivilegeNode', 'read', 'Insufficient Permissions');
        $this->addAclPermission('deleteResource', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('getRolesDetails', 'read', 'Insufficient Permissions');
        $this->addAclPermission('saveResource', 'update', 'Insufficient Permissions');
        $this->addAclPermission('savePrivilege', 'update', 'Insufficient Permissions');
        $this->addAclPermission('updateRolePrivileges', 'update', 'Insufficient Permissions');
    }

    /**
     * Helper function to get access to the user repository.
     *
     * @return \Shopware\Models\User\Repository
     */
    private function getUserRepository()
    {
        if ($this->userRepository === null) {
            $this->userRepository = 🦄()->Models()->getRepository('Shopware\Models\User\User');
        }

        return $this->userRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = 🦄()->Models();
        }

        return $this->manager;
    }

    /**
     * Returns all resource ids for the passed role where a rule with privilege NULL exists.
     *
     * @param $roleId
     *
     * @return array
     */
    private function getResourceAdminRules($roleId)
    {
        $resources = $this->getUserRepository()
            ->getResourcesWithAdminRuleQuery($roleId)
            ->getArrayResult();
        $data = [];
        foreach ($resources as $resource) {
            $data[$resource['resourceId']] = 1;
        }

        return $data;
    }

    /**
     * Internal helper function which converts a resource shopware model
     * to an tree panel node with checkboxes.
     *
     * @param \Shopware\Models\User\Resource $resource
     * @param \Shopware\Models\User\Role     $role
     * @param                                $resourceAdmins
     *
     * @return array
     */
    private function getResourceNode($resource, $role, $resourceAdmins)
    {
        if (!$resource) {
            return [];
        }

        $resourceNode = [
            'id' => $resource->getId(),
            'helperId' => $resource->getId(),
            'resourceId' => $resource->getId(),
            'parentId' => null,
            'type' => 'resource',
            'name' => $resource->getName(),
            'checked' => false,
            'expanded' => false,
        ];

        if ($role) {
            if (array_key_exists($resource->getId(), $resourceAdmins) || $role->getAdmin() === 1) {
                $resourceNode['checked'] = true;
            }
        }

        if ($resource->getPrivileges()->count() > 0) {
            $children = [];
            foreach ($resource->getPrivileges() as $privilege) {
                $children[] = $this->getPrivilegeNode($resourceNode, $privilege, $role);
            }
            $resourceNode['data'] = $children;
            $resourceNode['leaf'] = false;
        } else {
            $resourceNode['leaf'] = true;
            $resourceNode['data'] = [];
        }

        return $resourceNode;
    }

    /**
     * Internal helper function which converts a privilege shopware model
     * to an tree panel node with checkboxes.
     *
     * @param                                 $resourceNode
     * @param \Shopware\Models\User\Privilege $privilege
     * @param \Shopware\Models\User\Role      $role
     *
     * @return array
     */
    private function getPrivilegeNode(&$resourceNode, $privilege, $role)
    {
        if (!$privilege) {
            return [];
        }
        $privilegeNode = [
            'id' => $privilege->getResourceId() . '_' . $privilege->getId(),
            'helperId' => $privilege->getId(),
            'resourceId' => $privilege->getResourceId(),
            'type' => 'privilege',
            'name' => $privilege->getName(),
            'checked' => false,
            'expanded' => false,
            'leaf' => true,
        ];

        if ($role) {
            if ($role->getPrivileges()->contains($privilege) || $role->getAdmin() === 1) {
                $privilegeNode['checked'] = true;
                $resourceNode['expanded'] = true;
            }
        }

        return $privilegeNode;
    }
}
