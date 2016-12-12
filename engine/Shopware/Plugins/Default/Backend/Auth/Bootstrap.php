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
        $auth = Shopware()->Container()->get('Auth');
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
        $container = $this->Application()->Container();

        $locale = $this->getCurrentLocale();
        $container->get('Locale')->setLocale($locale->toString());
        $container->get('Snippets')->setLocale($locale);
        $template = $container->get('Template');
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
        $container = $this->Application()->Container();
        if ($this->acl === null) {
            $this->acl = $container->get('Acl');
        }
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $this->aclRole = $identity->role;
        }

        /** @var $engine Enlight_Template_Manager */
        $engine = $container->get('Template');
        $engine->unregisterPlugin(
            Enlight_Template_Manager::PLUGIN_FUNCTION,
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
     * @return \Shopware\Components\Session\SessionInterface
     */
    public function onInitResourceBackendSession(Enlight_Event_EventArgs $args)
    {
        return $this->get('session');
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
        /** @var \Shopware\Components\Session\SessionInterface $session */
        $session = $this->get('BackendSession');

        $resource = Shopware_Components_Auth::getInstance();
        $adapter = new Shopware_Components_Auth_Adapter_Default($session);
        $storage = new Shopware_Components_Auth_Storage_Session($session);
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
        $auth = Shopware()->Container()->get('Auth');
        if (Enlight_Components_Session::sessionExists()) {
            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();
                if (isset($user->locale)) {
                    return $user->locale;
                }
            }
        }

        $default = $this->getDefaultLocale();
        $locale = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale')->find($default);

        return $locale;
    }
}
