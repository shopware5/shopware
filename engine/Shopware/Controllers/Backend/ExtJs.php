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
 * Shopware ExtJs Controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_ExtJs extends Enlight_Controller_Action
{
    /**
     * @var Shopware_Plugins_Backend_Auth_Bootstrap
     */
    protected $auth;

    /**
     * Array with all permissions to check in this controller
     *
     * @var array
     */
    protected $aclPermissions = array();

    /**
     * Holds optionally acl error message
     *
     * @var string
     */
    protected $errorMessage;

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
            ->setParseParams(array('group', 'sort', 'filter'))
            ->setPadding($this->Request()->targetField);

        // Call controller acl rules (user - defined)
        $this->initAcl();
    }

    /**
     * Enable json renderer for index / load action
     * Check acl rules
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), array('index', 'load', 'skeleton', 'extends'))) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * This method must be overwritten by any module which wants to use ACL.
     *
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->setAclResourceName('name_of_your_resource'); // Default to controller base name
     * $this->addAclPermission('name_of_action_with_action_prefix','name_of_assigned_privilege','optionaly error message');
     * // $this->addAclPermission('indexAction','read','Ops. You have no permission to view that...');
     * </code>
     */
    protected function initAcl()
    {
    }

    /**
     * Returns all acl permissions
     * @return array
     */
    public function getAclRules()
    {
        return $this->aclPermissions;
    }

    /**
     * Add an acl permission rule to $this->aclPermissions array
     * Permissions will be checked automatically.
     *
     * @param $action string Name of action with action prefix
     * @param $privilege string Name of privilege as you have set in s_core_acl_privileges
     * @param $errorMessage string Optionally error message to show if permission denied
     */
    protected function addAclPermission($action, $privilege, $errorMessage = '')
    {
        if (strpos($action, 'Action') !== false) {
            $action = str_replace('Action', '', $action);
        }

        $this->aclPermissions[$action] = array(
            'privilege' => $privilege,
            'errorMessage' => $errorMessage
        );
    }

    /**
     * Helper method to do particular in code acl checks
     *
     * @param null|string $privilege Name of privilege
     * @param null|string|Zend_Acl_Role_Interface $resource
     * @param null|string|Zend_Acl_Resource_Interface $role
     * @return boolean
     */
    protected function _isAllowed($privilege, $resource = null, $role = null)
    {
        return Shopware()->Plugins()->Backend()->Auth()->isAllowed(array(
            'privilege' => $privilege,
            'resource' => $resource,
            'role' => $role
        ));
    }

    /**
     * Needs to be present for the script renderer
     */
    public function indexAction()
    {
        $identity = Shopware()->Auth()->getIdentity();
        $this->View()->assign('user', $identity, true);

        if ($this->Request()->get('file') == 'bootstrap') {
            $this->View()->assign('tinymceLang', $this->getTinyMceLang($identity), true);
        }
    }

    protected function getTinyMceLang($identity)
    {
        if (!$identity || !$identity->locale) {
            return 'en';
        }

        $attemptedLanguage = substr($identity->locale->getLocale(), 0, 2);

        if (file_exists(Shopware()->OldPath() . "engine/Library/TinyMce/langs/".$attemptedLanguage.".js")) {
            return $attemptedLanguage;
        }

        return 'en';
    }

    /**
     * Needs to be present for the script renderer
     */
    public function loadAction()
    {
    }

    public function extendsAction()
    {
        $request = $this->Request();
        $moduleName = 'backend';
        $controllerName = $this->Request()->getParam('baseController');

        $inflector = new Zend_Filter_Inflector(':module/:controller/:file:suffix');
        $inflector->setRules(array(
            ':module' => array('Word_CamelCaseToUnderscore', 'StringToLower'),
            ':controller' => array('Word_CamelCaseToUnderscore', 'StringToLower'),
            ':file' => array('Word_CamelCaseToUnderscore', 'StringToLower'),
            'suffix' => '.js'
        ));
        $inflector->setThrowTargetExceptionsOn(false);

        $fileNames = (array) $request->getParam('file');

        if (empty($fileNames)) {
            $fileNames = $request->getParam('f', array());
            $fileNames = explode('|', $fileNames);
        }

        if (empty($fileNames)) {
            return;
        }

        $this->Response()->setHeader('Content-Type', 'application/javascript; charset=utf-8', true);
        $template = 'snippet:string:';

        $this->View()->Engine()->setCompileId($this->View()->Engine()->getCompileId() . '_' . $this->Request()->getControllerName());

        foreach ($fileNames as $fileName) {
            // if string starts with "m/" replace with "model/"
            $fileName = preg_replace('/^m\//', 'model/', $fileName);
            $fileName = preg_replace('/^c\//', 'controller/', $fileName);
            $fileName = preg_replace('/^v\//', 'view/', $fileName);

            $fileName = ltrim(dirname($fileName) . '/' . basename($fileName, '.js'), '/.');
            if (empty($fileName)) {
                continue;
            }
            $templateBase = $inflector->filter(array(
                'module' => $moduleName,
                'controller' => $controllerName,
                'file' => $fileName)
            );

            $templateExtend = $inflector->filter(array(
                'module' => $moduleName,
                'controller' => $this->Request()->getControllerName(),
                'file' => $fileName)
            );
            if ($this->View()->templateExists($templateBase)) {
                $template .= '{include file="' . $templateBase. '"}' . "\n";
            }
            if ($this->View()->templateExists($templateExtend)) {
                $template .= '{include file="' . $templateExtend. '"}' . "\n";
            }
        }

        $toFind = $this->Request()->getParam('find');
        $toReplace = $this->Request()->getParam('replace');
        $toFind = rtrim($toFind, '.') . '.';
        $toReplace = rtrim($toReplace, '.') . '.';

        $this->View()->setTemplate();
        $template = $this->View()->fetch($template);
        $template = str_replace($toFind, $toReplace, $template);
        echo $template;
    }
}
