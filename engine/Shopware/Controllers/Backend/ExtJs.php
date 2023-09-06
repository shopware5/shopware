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

abstract class Shopware_Controllers_Backend_ExtJs extends Enlight_Controller_Action
{
    /**
     * Array with all permissions to check in this controller
     *
     * @var array<string, array{privilege: string, errorMessage: string}>
     */
    protected $aclPermissions = [];

    /**
     * Enable script renderer and json request plugin
     * Do acl checks
     *
     * @return void
     */
    public function init()
    {
        $this->Front()->Plugins()->ScriptRenderer()->setRender();
        $this->Front()->Plugins()->JsonRequest()
            ->setParseInput()
            ->setParseParams(['group', 'sort', 'filter'])
            ->setPadding($this->Request()->get('targetField'));

        // Call controller acl rules (user - defined)
        $this->initAcl();
    }

    /**
     * Enable json renderer for index / load action
     * Checks acl rules
     */
    public function preDispatch()
    {
        if (!\in_array($this->Request()->getActionName(), ['index', 'load', 'skeleton', 'extends'])) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * Returns all acl permissions
     *
     * @return array<string, array{privilege: string, errorMessage: string}>
     */
    public function getAclRules()
    {
        return $this->aclPermissions;
    }

    /**
     * Needs to be present for the script renderer
     *
     * @return void
     */
    public function indexAction()
    {
        $identity = Shopware()->Container()->get('auth')->getIdentity();
        $this->View()->assign('user', $identity, true);

        if ($this->Request()->get('file') === 'bootstrap') {
            $this->View()->assign('tinymceLang', $this->getTinyMceLang($identity), true);
        }

        $this->enableBrowserCache();
    }

    /**
     * Needs to be present for the script renderer
     *
     * @return void
     */
    public function loadAction()
    {
        $this->enableBrowserCache();
    }

    /**
     * This method must be overwritten by any module which wants to use ACL.
     *
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->setAclResourceName('name_of_your_resource'); // Default to controller base name
     * $this->addAclPermission('name_of_action_with_action_prefix','name_of_assigned_privilege','optionally error message');
     * // $this->addAclPermission('indexAction','read','Ops. You have no permission to view that...');
     * </code>
     *
     * @return void
     */
    protected function initAcl()
    {
    }

    /**
     * Add an acl permission rule to $this->aclPermissions array
     * Permissions will be checked automatically.
     *
     * @param string $action       Name of action with or without 'Action'-suffix
     * @param string $privilege    Name of privilege as you have set in s_core_acl_privileges
     * @param string $errorMessage Optionally error message to show if permission denied
     *
     * @return void
     */
    protected function addAclPermission($action, $privilege, $errorMessage = '')
    {
        if (strpos($action, 'Action') !== false) {
            $action = str_replace('Action', '', $action);
        }

        $this->aclPermissions[$action] = [
            'privilege' => $privilege,
            'errorMessage' => $errorMessage,
        ];
    }

    /**
     * Helper method to do particular in code acl checks
     *
     * @param string|null                             $privilege Name of privilege
     * @param string|Zend_Acl_Role_Interface|null     $resource
     * @param string|Zend_Acl_Resource_Interface|null $role
     *
     * @return bool
     */
    protected function _isAllowed($privilege, $resource = null, $role = null)
    {
        return Shopware()->Plugins()->Backend()->Auth()->isAllowed([
            'privilege' => $privilege,
            'resource' => $resource,
            'role' => $role,
        ]);
    }

    /**
     * @param mixed|null $identity
     *
     * @return string
     */
    protected function getTinyMceLang($identity)
    {
        if (!$identity || !$identity->locale) {
            return 'en';
        }

        $attemptedLanguage = substr($identity->locale->getLocale(), 0, 2);

        if (file_exists(Shopware()->DocPath() . 'engine/Library/TinyMce/langs/' . $attemptedLanguage . '.js')) {
            return $attemptedLanguage;
        }

        return 'en';
    }

    private function enableBrowserCache(): void
    {
        if ($this->container->getParameter('shopware.template.forceCompile')) {
            return;
        }

        $this->Response()->headers->set('cache-control', 'max-age=2592000, public', true);
    }
}
