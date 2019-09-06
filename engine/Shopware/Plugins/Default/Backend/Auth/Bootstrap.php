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

use Shopware\Components\DependencyInjection\Bridge\Db;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Session\PdoSessionHandler;
use Shopware\Models\Shop\Locale;

/**
 * Shopware Auth Plugin
 */
class Shopware_Plugins_Backend_Auth_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Is set to true if no authentication for the current request is needed
     *
     * @var bool
     */
    protected $noAuth = false;

    /**
     * Disable acl checks in backend controllers
     *
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
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_Auth', 'onInitResourceAuth');
        $this->subscribeEvent('Enlight_Controller_Action_PreDispatch', 'onPreDispatchBackend');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_BackendSession', 'onInitResourceBackendSession');

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(['name' => 'Core']);
        $form->setParent($parent);
        $form->setLabel('Backend');
        $form->setElement('select', 'backendLocales', [
            'store' => 'base.Locale',
            'label' => 'Auswählbare Sprachen',
            'value' => [1, 2],
            'required' => true,
            'multiSelect' => true,
        ]);
        $form->setElement('interval', 'backendTimeout', [
            'label' => 'Timeout',
            'required' => true,
            'value' => 7200,
        ]);

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
     * @param array $params
     *
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
     *
     * @throws Enlight_Controller_Exception
     */
    public function onPreDispatchBackend(Enlight_Event_EventArgs $args)
    {
        $this->action = $args->getSubject();
        $this->request = $this->action->Request();
        $this->aclResource = strtolower($this->request->getControllerName());

        if ($this->aclResource === 'error' || $this->request->getModuleName() !== 'backend') {
            return;
        }

        if ($this->shouldAuth()) {
            if ($this->checkAuth() === null) {
                if ($this->request->isXmlHttpRequest()) {
                    throw new Enlight_Controller_Exception('Unauthorized', 401);
                }
                $this->action->redirect('backend/');
            }
        } else {
            $this->initLocale();
        }
    }

    /**
     * @throws Enlight_Controller_Exception
     *
     * @return Shopware_Components_Auth|null
     */
    public function checkAuth()
    {
        /** @var Shopware_Components_Auth $auth */
        $auth = Shopware()->Container()->get('auth');
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
                $test = ['privilege' => 'read'];
            }

            if (!$this->isAllowed($test)) {
                throw new Enlight_Controller_Exception(
                    $test['errorMessage'] ?: 'Permission denied',
                    401
                );
            }

            return $auth;
        }

        return null;
    }

    /**
     * Register acl plugin
     *
     * @param Zend_Auth $auth
     *
     * @throws \Exception
     * @throws \SmartyException
     */
    public function registerAclPlugin($auth)
    {
        $container = $this->Application()->Container();
        if ($this->acl === null) {
            $this->acl = $container->get('acl');
        }
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $this->aclRole = $identity->role;
        }

        /** @var Enlight_Template_Manager $engine */
        $engine = $container->get('template');
        $engine->unregisterPlugin(
            Smarty::PLUGIN_FUNCTION,
            'acl_is_allowed'
        );
        $engine->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'acl_is_allowed',
            [$this, 'isAllowed']
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
                ++$orderIndex;
            }
            $orderCriteria .= 'ELSE ' . $orderIndex . ' END ';

            // For each browser locale, get exact or similar
            // filtered by allowed backend locales
            // ordered by exact match from browser
            $sql = 'SELECT id FROM s_core_locales
                WHERE locale LIKE :browserLocale AND id IN (' . $quotedBackendLocale . ')
                ORDER BY CASE locale ' . $orderCriteria . ' LIMIT 1';

            foreach ($browserLocales as $key => $locale) {
                $fetchResult = Shopware()->Db()->fetchOne($sql, [
                    'browserLocale' => $locale . '%',
                ]);

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
        $locales = $this->Config()->get('backendLocales', [1]);
        if ($locales instanceof Enlight_Config) {
            $locales = $locales->toArray();
        }

        return $locales;
    }

    /**
     * @throws Exception
     *
     * @return Enlight_Components_Session_Namespace
     */
    public function onInitResourceBackendSession(Enlight_Event_EventArgs $args)
    {
        $options = $this->getSessionOptions();
        $saveHandler = $this->createSaveHandler(Shopware()->Container());
        if ($saveHandler) {
            session_set_save_handler($saveHandler);
        }

        Enlight_Components_Session::start($options);

        return new Enlight_Components_Session_Namespace('ShopwareBackend');
    }

    /**
     * Initiate shopware auth resource
     * database adapter by default
     *
     * @throws \Exception
     * @throws \SmartyException
     * @throws \Enlight_Exception
     *
     * @return \Zend_Auth
     */
    public function onInitResourceAuth(Enlight_Event_EventArgs $args)
    {
        Shopware()->Container()->load('backendsession');

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
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * Init backend locales
     *
     * @throws \Exception
     */
    protected function initLocale()
    {
        $container = $this->Application()->Container();

        $locale = $this->getCurrentLocale();
        $container->get('locale')->setLocale($locale->toString());
        $container->get('snippets')->setLocale($locale);
        $template = $container->get('template');
        $baseHash = $this->request->getScheme() . '://'
                  . $this->request->getHttpHost()
                  . $this->request->getBaseUrl() . '?'
                  . $container->getParameter('shopware.release.revision');
        $baseHash = substr(sha1($baseHash), 0, 5);
        $template->setCompileId('backend_' . $locale->toString() . '_' . $baseHash);

        if ($this->action !== null && $this->action->View()->hasTemplate()) {
            $this->action->View()->Template()->setCompileId($template->getCompileId());
        }
    }

    /**
     * Loads current user's locale or, if none exists, the default fallback
     *
     * @throws \Exception
     *
     * @return \Shopware\Models\Shop\Locale
     */
    protected function getCurrentLocale()
    {
        $options = $this->getSessionOptions();
        $modelManager = $this->get('models');

        Enlight_Components_Session::setOptions($options);

        if (Enlight_Components_Session::sessionExists()) {
            $auth = $this->get('auth');
            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();

                if ($user->locale instanceof __PHP_Incomplete_Class) {
                    $user->locale = $modelManager->getRepository(Locale::class)->find($user->localeID);
                }

                if (isset($user->locale)) {
                    return $user->locale;
                }
            }
        }

        $default = $this->getDefaultLocale();

        return $modelManager->getRepository(Locale::class)->find($default);
    }

    /**
     * Filters and transforms the session options array
     * so it complies with the format expected by Enlight_Components_Session
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     *
     * @return array
     */
    private function getSessionOptions()
    {
        $options = Shopware()->Container()->getParameter('shopware.backendsession');

        if ($this->request !== null && !isset($options['cookie_path'])) {
            $options['cookie_path'] = rtrim($this->request->getBaseUrl(), '/') . '/backend/';
        }
        if (empty($options['gc_maxlifetime'])) {
            $backendTimeout = $this->Config()->get('backendTimeout', 60 * 90);
            // 10 years
            $options['gc_maxlifetime'] = (int) $backendTimeout ?: 315360000;
        }
        unset($options['locking']);

        return $options;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return \SessionHandlerInterface|null
     */
    private function createSaveHandler(Container $container)
    {
        $sessionOptions = $container->getParameter('shopware.backendsession');
        if (isset($sessionOptions['save_handler']) && $sessionOptions['save_handler'] !== 'db') {
            return null;
        }

        $dbOptions = $container->getParameter('shopware.db');
        $conn = Db::createPDO($dbOptions);

        return new PdoSessionHandler(
            $conn,
            [
                'db_table' => 's_core_sessions_backend',
                'db_id_col' => 'id',
                'db_data_col' => 'data',
                'db_expiry_col' => 'expiry',
                'db_time_col' => 'modified',
                'lock_mode' => $sessionOptions['locking'] ? PdoSessionHandler::LOCK_TRANSACTIONAL : PdoSessionHandler::LOCK_NONE,
            ]
        );
    }
}
