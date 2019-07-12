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

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Password\Manager;
use Shopware\Models\User\Privilege;
use Shopware\Models\User\Resource;
use Shopware\Models\User\Role;
use Shopware\Models\User\Rule;
use Shopware\Models\User\User;

class Shopware_Controllers_Backend_UserManager extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\User\Repository
     */
    protected $userRepository = null;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    public function __construct(
        ModelManager $modelManager,
        Manager $manager,
        Shopware_Components_Snippet_Manager $snippetManager
    ) {
        $this->modelManager = $modelManager;
        $this->manager = $manager;
        $this->snippetManager = $snippetManager;
    }

    /**
     * Some actions from this controller must be password verified before being able to execute them,
     * this means that the administrator password must be entered again into a dialog box in order to execute
     * the requested action.
     *
     * @see Shopware_Controllers_Backend_Login::validatePasswordAction()
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $calledAction = $this->Request()->getActionName();

        if (
            Shopware()->Plugins()->Backend()->Auth()->shouldAuth()
            && $this->isPasswordConfirmProtectedAction($calledAction)
            && !$this->container->get('backendsession')->offsetGet('passwordVerified')
        ) {
            return $this->forward('passwordConfirmationRequired');
        }
    }

    /**
     * Displays a JSON string indicating failure for password confirmation
     */
    public function passwordConfirmationRequiredAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer();

        $this->View()->assign([
            'success' => false,
            'data' => [],
        ]);
    }

    /**
     * If the requested action is meant to be password verified, unset the session flag in order
     * for it not to persist in other requests. In this way, the password verification process will be triggered
     * again.
     *
     * @see Shopware_Controllers_Backend_Login::validatePasswordAction()
     */
    public function postDispatch()
    {
        parent::postDispatch();

        $calledAction = $this->Request()->getActionName();
        $backendSession = $this->container->get('backendsession');

        if (
            Shopware()->Plugins()->Backend()->Auth()->shouldAuth()
            && $this->isPasswordConfirmProtectedAction($calledAction)
            && $backendSession->offsetGet('passwordVerified')
        ) {
            $backendSession->offsetUnset('passwordVerified');
        }
    }

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

        if (!$this->_isAllowed('create') && !$this->_isAllowed('update')) {
            unset($data['apiKey'], $data['sessionId']);
        }

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
        $limit = (empty($params['limit'])) ? 20 : (int) $params['limit'];
        $offset = (empty($params['start'])) ? 0 : (int) $params['start'];
        $filter = (empty($params['search'])) ? null : $params['search'];

        if (!empty($filter)) {
            $offset = 0;
        }

        $query = $this->getUserRepository()
            ->getUsersQuery($filter, $limit, $offset);

        // Returns the total count of the query
        $totalResult = $this->modelManager->getQueryCount($query);

        // Returns the customer data
        $customers = $query->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $customers,
            'total' => $totalResult,
        ]);
    }

    /**
     * Delete role from database
     * Identified by request parameter id
     *
     * @throws Exception
     */
    public function deleteRoleAction()
    {
        $rolesRepository = $this->modelManager->getRepository(Role::class);
        $roleId = $this->Request()->getParam('id');

        if ($this->get('dbal_connection')->fetchColumn('SELECT COUNT(id) > 0 FROM s_core_auth WHERE roleID = :roleID', ['roleID' => $roleId])) {
            throw new Exception('Role has assigned users');
        }

        $entity = $rolesRepository->find($roleId);
        $this->modelManager->remove($entity);
        // Performs all of the collected actions.
        $this->modelManager->flush();

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    /**
     * Update / create role
     * All params will be get from request object
     */
    public function updateRoleAction()
    {
        $id = $this->Request()->getParam('id', null);
        $rolesRepository = $this->modelManager->getRepository(Role::class);

        if (!empty($id)) {
            /** @var Role $role */
            $role = $rolesRepository->find((int) $id);
        } else {
            $role = new Role();
        }
        $params = $this->Request()->getParams();

        if ($params['enabled'] === 'on' || $params['enabled'] === true || $params['enabled'] === 1) {
            $params['enabled'] = true;
        } else {
            $params['enabled'] = false;
        }

        if ($params['admin'] === 'on' || $params['admin'] === true || $params['admin'] === 1) {
            $params['admin'] = true;
        } else {
            $params['admin'] = false;
        }

        $role->fromArray($params);

        $this->modelManager->persist($role);
        $this->modelManager->flush();

        // Check if admin flag is set or unset
        if ($params['admin'] == true) {
            Shopware()->Db()->query('
                INSERT IGNORE INTO s_core_acl_roles (roleID,resourceID,privilegeID) VALUES (?,?,?)
                ', [$role->getId(), null, null]);
        } else {
            $query = $this->getUserRepository()->getAdminRuleDeleteQuery($role->getId());
            $query->execute();
        }

        $this->View()->assign([
            'success' => true,
            'data' => $this->modelManager->toArray($role),
        ]);
    }

    /**
     * Get all roles available in database
     * Strip roles that have parentID set - cause
     * this roles are assigned to particular users
     *
     * Sample json return value
     *                <code>
     *                {"success":true,"data":[{"id":1,"parentId":null,"name":"Administrators","description":"Default group that gains access to all shop functions","source":"build-in","enabled":1,"admin":1},{"id":2,"parentId":null,"name":"Test-Group1A","description":"Group that has restricted access ","source":"test","enabled":0,"admin":0},{"id":3,"parentId":null,"name":"Test-Group2","description":"Group that has restricted access ","source":"test","enabled":0,"admin":0},{"id":4,"parentId":3,"name":"Test-Group3","description":"Group that has restricted access ","source":"test","enabled":1,"admin":0}],"total":4}
     *                </code>
     */
    public function getRolesAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 20);
        $offset = (int) $this->Request()->getParam('start', 0);
        $id = $this->Request()->getParam('id', null);

        if ($id !== null) {
            $queryBuilder = $this->getUserRepository()->getRolesQueryBuilder();
            $query = $queryBuilder
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->andWhere('roles.id = :role_id')
                ->setParameter(':role_id', (int) $id)
                ->getQuery();
            $count = 1;
        } else {
            $query = $this->getUserRepository()
                ->getRolesQuery($offset, $limit);
            $count = Shopware()->Models()->getQueryCount($query);
        }

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
        $id = $this->Request()->getParam('id');
        $isNewUser = false;

        if (!empty($id)) {
            /** @var User $user */
            $user = $this->getUserRepository()->find((int) $id);
        } else {
            $user = new User();
            $isNewUser = true;
        }

        $params = $this->Request()->getParams();
        if (!empty($params['password'])) {
            $params['encoder'] = $this->manager->getDefaultPasswordEncoderName();
            $params['password'] = $this->manager->encodePassword($params['password'], $params['encoder']);
        } else {
            unset($params['password']);
        }

        $user->fromArray($params);

        $this->modelManager->persist($user);
        $this->modelManager->flush();

        if ($isNewUser) {
            $sql = 'INSERT INTO `s_core_widget_views` (`widget_id`, `auth_id`) VALUES ((SELECT id FROM `s_core_widgets` WHERE `name` = :widgetName LIMIT 1), :userId);';
            Shopware()->Db()->executeQuery(
                $sql,
                [
                    ':widgetName' => 'swag-shopware-news-widget',
                    ':userId' => $user->getId(),
                ]
            );
        }

        $this->View()->assign([
            'success' => true,
            'data' => $this->modelManager->toArray($user),
        ]);
    }

    /**
     * Unlocks a backend user
     */
    public function unlockUserAction()
    {
        $userId = (int) $this->Request()->getParam('userId');

        try {
            $connection = $this->container->get('dbal_connection');
            $connection->executeQuery('UPDATE s_core_auth SET lockedUntil = NOW(), failedLogins = 0 WHERE id = ?', [$userId]);
        } catch (Exception $e) {
            $this->View()->assign('success', false);

            return;
        }

        $this->View()->assign('success', true);
    }

    /**
     * Deletes a backend user from the database
     *
     * @throws Exception
     */
    public function deleteUserAction()
    {
        // Get posted user
        $userID = $this->Request()->getParam('id');
        $getCurrentIdentity = $this->get('Auth')->getIdentity();

        // Backend users shall not delete their current login
        if ($userID == $getCurrentIdentity->id) {
            throw new Exception('You can not delete your current account');
        }

        $entity = $this->getUserRepository()->find($userID);
        $this->modelManager->remove($entity);

        // Performs all of the collected actions.
        $this->modelManager->flush();

        $this->View()->assign([
            'success' => true,
            'data' => $this->Request()->getParams(),
        ]);
    }

    /**
     * Event listener method which is used from the rules store to load first time the
     * resource and on node expand the privileges of the passed resource id.
     */
    public function getResourcesAction()
    {
        $search = $this->Request()->getParam('search');

        $resources = $this->getUserRepository()
            ->getResourcesQuery($search)
            ->getResult();

        $data = [];
        $role = $this->Request()->getParam('role');
        $resourceAdmins = [];

        /** @var \Shopware\Models\User\Role $role */
        if ($role !== null && is_numeric($role)) {
            $role = $this->modelManager->find(Role::class, $role);

            $repository = $this->modelManager->getRepository(Rule::class);
            $adminRole = $repository->findOneBy([
                'roleId' => $role->getId(),
                'resourceId' => null,
                'privilegeId' => null,
            ]);

            $resourceAdmins = $this->getResourceAdminRules($role->getId());

            // The admin property is temporary used to flag the passed role as admin role
            if ($adminRole instanceof Rule && $adminRole->getRoleId()) {
                $role->setAdmin(1);
            } else {
                $role->setAdmin(0);
            }
        }

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
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->snippetManager->getNamespace('backend/user_manager');

        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_resource_passed', 'No valid resource id passed'),
            ]);

            return;
        }

        // Remove the privilege
        $query = $this->getUserRepository()->getPrivilegeDeleteByResourceIdQuery($id);
        $query->execute();

        // Clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByResourceIdQuery($id);
        $query->setParameter(1, $id);
        $query->execute();

        // Clear mapping table s_core_acl_roles
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
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->snippetManager->getNamespace('backend/user_manager');

        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_privilege_passed', 'No valid privilege id passed'),
            ]);

            return;
        }

        // Clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByPrivilegeIdQuery($id);
        $query->execute();

        // Remove the privilege
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
        unset($role);

        $this->View()->assign([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user wants to create a new resource.
     */
    public function saveResourceAction()
    {
        $resource = new Resource();
        $data = $this->Request()->getParams();
        $resource->fromArray($data);

        $this->modelManager->persist($resource);
        $this->modelManager->flush();

        $data = $this->modelManager->toArray($resource);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user wants to create a new resource.
     */
    public function savePrivilegeAction()
    {
        $privilege = new Privilege();
        $data = $this->Request()->getParams();
        $privilege->fromArray($data);

        $this->modelManager->persist($privilege);
        $this->modelManager->flush();

        $data = $this->modelManager->toArray($privilege);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Event listener method of the user manager backend module, which is fired
     * when the user changes the checkboxes of the rules tree and wants to assign the selected
     * privileges to the selected role.
     */
    public function updateRolePrivilegesAction()
    {
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->snippetManager->getNamespace('backend/user_manager');

        $id = $this->Request()->getParam('id');
        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_role_passed', 'No valid role id passed'),
            ]);

            return;
        }

        // Check if role exist
        /** @var \Shopware\Models\User\Role|null $role */
        $role = $this->modelManager->find(Role::class, $id);

        if ($role === null) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_role_passed', 'No valid role id passed'),
            ]);

            return;
        }
        // Get new role rules
        $newRules = $this->Request()->getParam('privileges', null);

        // Iterate the new rules and create shopware models
        foreach ($newRules as $newRule) {
            $rule = new Rule();
            $rule->setRole($role);

            if (isset($newRule['resourceId'])) {
                /** @var \Shopware\Models\User\Resource $resource */
                $resource = Shopware()->Models()->find(Resource::class, $newRule['resourceId']);
                $rule->setResource($resource);
            }
            if (isset($newRule['privilegeId'])) {
                /** @var \Shopware\Models\User\Privilege $privilege */
                $privilege = Shopware()->Models()->find(Privilege::class, $newRule['privilegeId']);
                $rule->setPrivilege($privilege);
            } else {
                $rule->setPrivilege(null);
            }
            $this->modelManager->persist($rule);
        }

        // Clear mapping table s_core_acl_roles
        $query = $this->getUserRepository()->getRuleDeleteByRoleIdQuery($role->getId());
        $query->execute();

        $this->modelManager->flush();

        $data = $this->modelManager->toArray($role);

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
     * Verifies if an action name must be password confirm protected
     *
     * @param string $name
     *
     * @return bool
     */
    private function isPasswordConfirmProtectedAction($name)
    {
        return in_array(strtolower($name), $this->getPasswordConfirmProtectedActions(), true);
    }

    /**
     * Returns an array of actions which must be password confirmed.
     *
     * @return array
     */
    private function getPasswordConfirmProtectedActions()
    {
        return [
            'deleterole',
            'updaterole',
            'updateuser',
            'deleteuser',
            'deleteresource',
            'deleteprivilege',
            'updateroleprivilege',
            'saveprivilege',
        ];
    }

    /**
     * Helper function to get access to the user repository.
     *
     * @return \Shopware\Models\User\Repository
     */
    private function getUserRepository()
    {
        if ($this->userRepository === null) {
            $this->userRepository = $this->modelManager->getRepository(User::class);
        }

        return $this->userRepository;
    }

    /**
     * Returns all resource ids for the passed role where a rule with privilege NULL exists.
     *
     *
     * @return array
     */
    private function getResourceAdminRules(int $roleId)
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
     * @return array
     */
    private function getResourceNode(?Resource $resource, ?Role $role, array $resourceAdmins)
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
                $resourceNode['expanded'] = true;
            }
        }

        if (count($resource->getPrivileges()) > 0) {
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
     * @return array
     */
    private function getPrivilegeNode(array &$resourceNode, ?Privilege $privilege, ?Role $role)
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
            'checked' => $resourceNode['checked'] ? true : false,
            'expanded' => false,
            'leaf' => true,
            'requirements' => [],
        ];

        if (count($privilege->getRequirements()) > 0) {
            $requirements = [];
            foreach ($privilege->getRequirements() as $requirement) {
                $requirements[] = $requirement->getId();
            }
            $privilegeNode['requirements'] = $requirements;
        }

        if ($role) {
            if ($role->getPrivileges()->contains($privilege) || $role->getAdmin() === 1) {
                $privilegeNode['checked'] = true;
                $resourceNode['expanded'] = true;
            }
        }

        return $privilegeNode;
    }
}
