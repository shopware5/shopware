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

namespace Shopware\Components\Auth;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Components\DependencyInjection\Bridge\Db;
use Shopware\Components\DependencyInjection\Container;

class BackendAuthSubscriber implements SubscriberInterface
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
     * @var \Zend_Acl
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
     * @var \Enlight_Controller_Action
     */
    protected $action;

    /**
     * The current request instance
     *
     * @var \Enlight_Controller_Request_Request
     */
    protected $request;

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_InitResource_Auth' => 'onInitResourceAuth',
            'Enlight_Controller_Action_PreDispatch' => 'onPreDispatchBackend',
        ];
    }

    /**
     * Returns true if and only if the Role has access to the Resource
     *
     * @param $params
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
     * @param Enlight_Event_EventArgs $args
     *
     * @throws \Enlight_Controller_Exception
     */
    public function onPreDispatchBackend(Enlight_Event_EventArgs $args)
    {
        $this->action = $args->getSubject();
        $this->request = $this->action->Request();
        $this->aclResource = strtolower($this->request->getControllerName());

        if ($this->request->getModuleName() != 'backend'
            || in_array($this->aclResource, ['error'])) {
            return;
        }

        if ($this->shouldAuth()) {
            if ($this->checkAuth() === null) {
                if ($this->request->isXmlHttpRequest()) {
                    throw new \Enlight_Controller_Exception('Unauthorized', 401);
                }
                $this->action->redirect('backend/');
            }
        } else {
            $this->initLocale();
        }
    }

    /**
     * @throws \Enlight_Controller_Exception
     *
     * @return null|\Shopware_Components_Auth
     */
    public function checkAuth()
    {
        /** @var $auth \Shopware_Components_Auth */
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
            if ($this->action instanceof \Shopware_Controllers_Backend_ExtJs) {
                $rules = $this->action->getAclRules();
            }
            if (isset($rules[$actionName])) {
                $test = $rules[$actionName];
            } else {
                $test = ['privilege' => 'read'];
            }

            if (!$this->isAllowed($test)) {
                throw new \Enlight_Controller_Exception(
                    $test['errorMessage'] ?: 'Permission denied',
                    401
                );
            }

            return $auth;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        $backendLocales = $this->getLocales();
        $browserLocales = array_keys(\Zend_Locale::getBrowser());

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
        $locales = Shopware()->Container()->get('config')->get('backendLocales', [1]);
        if ($locales instanceof \Enlight_Config) {
            $locales = $locales->toArray();
        }

        return $locales;
    }

    /**
     * Initiate shopware auth resource
     * database adapter by default
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @return \Zend_Auth
     */
    public function onInitResourceAuth(Enlight_Event_EventArgs $args)
    {
        Shopware()->Container()->load('backend_session');

        $resource = \Shopware_Components_Auth::getInstance();
        $adapter = new \Shopware_Components_Auth_Adapter_Default();
        $storage = new \Zend_Auth_Storage_Session('Shopware', 'Auth');
        $resource->setBaseAdapter($adapter);
        $resource->addAdapter($adapter);
        $resource->setStorage($storage);

        $this->registerAclPlugin($resource);

        return $resource;
    }

    /**
     * Init backend locales
     */
    private function initLocale()
    {
        $container = Shopware()->Container();

        $locale = $this->getCurrentLocale();
        $container->get('Locale')->setLocale($locale->toString());
        $container->get('Snippets')->setLocale($locale);
        $template = $container->get('Template');
        $baseHash = $this->request->getScheme() . '://'
            . $this->request->getHttpHost()
            . $this->request->getBaseUrl() . '?'
            . \Shopware::REVISION;

        $baseHash = substr(sha1($baseHash), 0, 5);
        $template->setCompileId('backend_' . $locale->toString() . '_' . $baseHash);

        if ($this->action !== null && $this->action->View()->hasTemplate()) {
            $this->action->View()->Template()->setCompileId($template->getCompileId());
        }
    }

    /**
     * Loads current user's locale or, if none exists, the default fallback
     *
     * @return \Shopware\Models\Shop\Locale
     */
    private function getCurrentLocale()
    {
        \Enlight_Components_Session::setOptions(
            $this->getSessionOptions()
        );

        if (\Enlight_Components_Session::sessionExists()) {
            $auth = Shopware()->Container()->get('auth');
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

    /**
     * Filters and transforms the session options array
     * so it complies with the format expected by Enlight_Components_Session
     *
     * @return array
     */
    private function getSessionOptions()
    {
        $options = Shopware()->Container()->getParameter('shopware.backendsession');
        $config = Shopware()->Container()->get('config');

        if (!isset($options['cookie_path']) && $this->request !== null) {
            $options['cookie_path'] = rtrim($this->request->getBaseUrl(), '/') . '/backend/';
        }
        if (empty($options['gc_maxlifetime'])) {
            $backendTimeout = $config->get('backendTimeout', 60 * 90);
            $options['gc_maxlifetime'] = (int) $backendTimeout ?: PHP_INT_MAX;
        }
        unset($options['locking']);

        return $options;
    }

    /**
     * Register acl plugin
     *
     * @param \Zend_Auth $auth
     */
    private function registerAclPlugin($auth)
    {
        $container = Shopware()->Container();
        if ($this->acl === null) {
            $this->acl = $container->get('Acl');
        }
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $this->aclRole = $identity->role;
        }

        /** @var $engine \Enlight_Template_Manager */
        $engine = $container->get('template');
        $engine->unregisterPlugin(
            \Smarty::PLUGIN_FUNCTION,
            'acl_is_allowed'
        );

        $engine->registerPlugin(
            \Enlight_Template_Manager::PLUGIN_FUNCTION,
            'acl_is_allowed',
            [$this, 'isAllowed']
        );
    }

    /**
     * Is authentication is necessary?
     *
     * @return bool
     */
    private function shouldAuth()
    {
        return !$this->noAuth;
    }
}
