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
 *
 * Shopware Auth Plugin
 */
class Shopware_Plugins_Backend_Auth_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Is set to true if no authentication for the current request is needed
     * @var bool
     */
    protected $noAuth = false;

    /**
     * Disable acl checks in backend controllers
     * @var bool
     */
    protected $noAcl = false;

    /**
     * The acl instance
     *
     * @var Zend_Acl
     */
    protected $acl;

    /**
     * The current acl role
     *
     * @var string
     */
    protected $aclRole;

    /**
     * The current acl resource
     *
     * @var string
     */
    protected $aclResource;

    /**
     * The current request instance
     *
     * @var Enlight_Controller_Action
     */
    protected $action;

    /**
     * The current request instance
     *
     * @var Enlight_Controller_Request_Request
     */
    protected $request;

    /**
     * Register shopware auth resource
     * create pre-dispatch hook to check backend permissions
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_Auth', 'onInitResourceAuth');
        $this->subscribeEvent('Enlight_Controller_Action_PreDispatch', 'onPreDispatchBackend');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_BackendSession', 'onInitResourceBackendSession');

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);
        $form->setLabel('Backend');
        $form->setElement('select', 'backendLocales', array(
            'store' => 'base.Locale',
            'label' => 'Auswählbare Sprachen',
            'value' => array(1, 2),
            'required' => true,
            'multiSelect' => true
        ));
        $form->setElement('interval', 'backendTimeout', array(
            'label' => 'Timeout',
            'required' => true,
            'value' => 7200
        ));

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return false;
    }

    /**
     * Returns true if and only if the Role has access to the Resource
     *
     * @param $params
     * @return bool
     */
    public function isAllowed($params)
    {
        if (empty($params) || $this->shouldUseAcl() == false) {
            return true;
        }
        $resourceId = isset($params['resource']) ? $params['resource'] : $this->aclResource;

        if (!$this->acl->has($resourceId)) {
            return true;
        }

        return $this->acl->isAllowed(
            isset($params['role']) ? $params['role'] : $this->aclRole,
            $resourceId,
            isset($params['privilege']) ? $params['privilege'] : null
        );
    }

    /**
     * Set local no auth property
     *
     * @param bool $flag
     */
    public function setNoAuth($flag = true)
    {
        $this->noAuth = $flag ? true : false;
    }

    /**
     * Set local no acl property
     *
     * @param bool $flag
     */
    public function setNoAcl($flag = true)
    {
        $this->noAcl = $flag ? true : false;
    }

    /**
     * Is authentication is necessary?
     *
     * @return bool
     */
    public function shouldAuth()
    {
        return !$this->noAuth;
    }

    /**
     * Is acl check is necessary?
     *
     * @return bool
     */
    public function shouldUseAcl()
    {
        return !$this->noAcl;
    }

    /**
     * This pre-dispatch event-hook checks backend permissions
     *
     * @param \Enlight_Event_EventArgs $args
     * @throws Enlight_Controller_Exception
     * @return void
     */
    public function onPreDispatchBackend(Enlight_Event_EventArgs $args)
    {
        $this->action = $args->getSubject();
        $this->request = $this->action->Request();
        $this->aclResource = strtolower($this->request->getControllerName());

        if ($this->request->getModuleName() != 'backend'
          || in_array($this->aclResource, array('error'))) {
            return;
        }

        if ($this->shouldAuth()) {
            if ($this->checkAuth() === null) {
                if ($this->request->isXmlHttpRequest()) {
                    throw new Enlight_Controller_Exception('Unauthorized', 401);
                } else {
                    $this->action->redirect('backend/');
                }
            }
        } else {
            $this->initLocale();
        }
    }

    /**
     * @return null|Shopware_Components_Auth
     * @throws Enlight_Controller_Exception
     */
    public function checkAuth()
    {
        /** @var $auth Shopware_Components_Auth */
        $auth = Shopware()->Auth();
        if ($auth->hasIdentity()) {
            $auth->refresh();
        }

        $this->initLocale();

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();

            $this->acl = Shopware()->Acl();
            $this->aclRole = $identity->role;

            if (!$this->acl->has($this->aclResource)) {
                return $auth;
            }

            $actionName = $this->request->getActionName();
            if ($this->action instanceof Shopware_Controllers_Backend_ExtJs) {
                $rules = $this->action->getAclRules();
            }
            if (isset($rules[$actionName])) {
                $test = $rules[$actionName];
            } else {
                $test = array('privilege' => 'read');
            }

            if (!$this->isAllowed($test)) {
                throw new Enlight_Controller_Exception(
                    $test['errorMessage'] ?: 'Permission denied',
                    401
                );
            } else {
                return $auth;
            }
        }
        return null;
    }

    /**
     * Init backend locales
     */
    protected function initLocale()
    {
        $bootstrap = $this->Application()->Bootstrap();

        $locale = $this->getCurrentLocale();
        $bootstrap->getResource('Locale')->setLocale($locale->toString());
        $bootstrap->getResource('Snippets')->setLocale($locale);
        $template = $bootstrap->getResource('Template');
        $baseHash = $this->request->getScheme() . '://'
                  . $this->request->getHttpHost()
                  . $this->request->getBaseUrl() . '?'
                  . Shopware::REVISION;
        $baseHash = substr(sha1($baseHash), 0, 5);
        $template->setCompileId('backend_' . $locale->toString() . '_' . $baseHash);

        if ($this->action !== null && $this->action->View()->hasTemplate()) {
            $this->action->View()->Template()->setCompileId($template->getCompileId());
        }
    }

    /**
     * Register acl plugin
     *
     * @param Zend_Auth $auth
     */
    public function registerAclPlugin($auth)
    {
        $bootstrap = $this->Application()->Bootstrap();
        if ($this->acl === null) {
            $this->acl = $bootstrap->getResource('Acl');
        }
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $this->aclRole = $identity->role;
        }

        /** @var $engine Enlight_Template_Manager */
        $engine = $bootstrap->getResource('Template');
        $engine->unregisterPlugin(
            Smarty::PLUGIN_FUNCTION,
            'acl_is_allowed'
        );
        $engine->registerPlugin(
            Enlight_Template_Manager::PLUGIN_FUNCTION,
            'acl_is_allowed',
            array($this, 'isAllowed')
        );
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        $backendLocales = $this->getLocales();
        $browserLocales = array_keys(Zend_Locale::getBrowser());

        if (!empty($browserLocales)) {
            $quotedBackendLocale = Shopware()->Db()->quote($backendLocales);
            $orderIndex = 1;
            $orderCriteria = '';
            foreach ($browserLocales as $browserLocale) {
                $orderCriteria .= 'WHEN ' . Shopware()->Db()->quote($browserLocale) . ' THEN ' . $orderIndex . ' ';
                $orderIndex++;
            }
            $orderCriteria .= 'ELSE ' . $orderIndex . ' END ';

            // For each browser locale, get exact or similar
            // filtered by allowed backend locales
            // ordered by exact match from browser
            $sql = 'SELECT id FROM s_core_locales
                WHERE locale LIKE :browserLocale AND id IN (' . $quotedBackendLocale . ')
                ORDER BY CASE locale ' . $orderCriteria . ' LIMIT 1';

            foreach ($browserLocales as $key => $locale) {
                $fetchResult = Shopware()->Db()->fetchOne($sql, array(
                    'browserLocale' => $locale . '%'
                ));

                if ($fetchResult) {
                    return $fetchResult;
                }
            }
        }

        // No match from the browser locales, fallback to default shop locale
        $defaultShopLocale = Shopware()->Db()->fetchOne(
            'SELECT locale_id
             FROM s_core_shops
             WHERE `default` = 1 AND active = 1
             LIMIT 1'
        );

        // if default shop locale is allowed, use it, otherwise use the first allowed locale
        return in_array($defaultShopLocale, $backendLocales) ? $defaultShopLocale : array_shift($backendLocales);
    }

    /**
     * Returns an array of valid locales
     *
     * @return array
     */
    public function getLocales()
    {
        $locales =  $this->Config()->get('backendLocales', array(1));
        if ($locales instanceof Enlight_Config) {
            $locales = $locales->toArray();
        }
        return $locales;
    }

    /**
     * Initiate shopware auth resource
     * database adapter by default
     *
     * @param Enlight_Event_EventArgs $args
     * @throws Exception
     * @return null|\Zend_Auth
     */
    public function onInitResourceBackendSession(Enlight_Event_EventArgs $args)
    {
        $options = Shopware()->Container()->getParameter('shopware.backendsession');

        $refererCheck = (bool) $options['referer_check'];
        $clientCheck = (bool) $options['client_check'];

        $options = $this->prepareSessionOptions($options);

        if (!isset($options['save_handler']) || $options['save_handler'] == 'db') {
            $config_save_handler = array(
                'name' => 's_core_sessions_backend',
                'primary' => 'id',
                'modifiedColumn' => 'modified',
                'dataColumn' => 'data',
                'lifetimeColumn' => 'expiry',
                'lifetime' => $options['gc_maxlifetime'] ? : PHP_INT_MAX
            );
            Enlight_Components_Session::setSaveHandler(
               new Enlight_Components_Session_SaveHandler_DbTable($config_save_handler)
            );
        }

        Enlight_Components_Session::start($options);

        if (
            !$this->isBackendHomepage()
            && $refererCheck
            && $this->shouldAuth()
            && ($referer = $this->request->getHeader('referer')) !== null
            && strpos($referer, 'http') === 0
        ) {
            $referer = substr($referer, 0, strpos($referer, '/backend/'));
            $referer .= '/backend/';

            if (!isset($_SESSION['__SW_REFERER'])) {
                $_SESSION['__SW_REFERER'] = $referer;
            } elseif (strpos($referer, $_SESSION['__SW_REFERER']) !== 0) {
                Enlight_Components_Session::destroy();
                throw new Exception('Referer check for backend session failed');
            }
        }
        if (
            $clientCheck
            && ($client = $this->request->getHeader('userAgent')) !== null
        ) {
            if (!isset($_SESSION['__SW_CLIENT'])) {
                $_SESSION['__SW_CLIENT'] = $client;
            } elseif ($client !==  $_SESSION['__SW_CLIENT']) {
                Enlight_Components_Session::destroy();
                throw new Exception('Client check for backend session failed');
            }
        }

        return new Enlight_Components_Session_Namespace('ShopwareBackend');
    }

    /**
     * Detects if the current request represents a request for the backend's homepage
     *
     * @return bool
     */
    private function isBackendHomepage()
    {
        if ($this->request->getParam('controller', 'index') != 'index') {
            return false;
        }
        if ($this->request->getParam('action', 'index') != 'index') {
            return false;
        }
        if ($this->request->getParam('module', 'backend') != 'backend') {
            return false;
        }

        $basePath = $this->request->getBasePath();
        $uri = $this->request->getRequestUri();

        return (str_replace($basePath, '', $uri) === '/backend/');
    }

    /**
     * Initiate shopware auth resource
     * database adapter by default
     *
     * @param Enlight_Event_EventArgs $args
     * @return null|\Zend_Auth
     */
    public function onInitResourceAuth(Enlight_Event_EventArgs $args)
    {
        $bootstrap = $this->Application()->Bootstrap();
        $bootstrap->loadResource('BackendSession');

        $resource = Shopware_Components_Auth::getInstance();
        $adapter = new Shopware_Components_Auth_Adapter_Default();
        $storage = new Zend_Auth_Storage_Session('Shopware', 'Auth');
        $resource->setBaseAdapter($adapter);
        $resource->addAdapter($adapter);
        $resource->setStorage($storage);

        $this->registerAclPlugin($resource);

        return $resource;
    }

    /**
     * Returns capabilities
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * Loads current user's locale or, if none exists, the default fallback
     *
     * @return \Shopware\Models\Shop\Locale
     */
    protected function getCurrentLocale()
    {
        $options = Shopware()->Container()->getParameter('shopware.backendsession');
        $options = $this->prepareSessionOptions($options);

        Enlight_Components_Session::setOptions($options);

        if (Enlight_Components_Session::sessionExists()) {
            $auth = Shopware()->Auth();
            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();
                if (isset($user->locale)) {
                    return $user->locale;
                }
            }
        }

        $default = $this->getDefaultLocale();
        $locale = Shopware()->Models()->getRepository(
            'Shopware\Models\Shop\Locale'
        )->find($default);

        return $locale;
    }

    /**
     * Filters and transforms the session options array
     * so it complies with the format expected by Enlight_Components_Session
     *
     * @param array $options
     * @return array
     */
    private function prepareSessionOptions($options)
    {
        if (!isset($options['cookie_path']) && $this->request !== null) {
            $options['cookie_path'] = rtrim($this->request->getBaseUrl(), '/').'/backend/';
        }
        if (empty($options['gc_maxlifetime'])) {
            $backendTimeout = $this->Config()->get('backendTimeout', 60 * 90);
            $options['gc_maxlifetime'] = (int) $backendTimeout;
        }

        unset($options['referer_check']);
        unset($options['client_check']);

        return $options;
    }
}
