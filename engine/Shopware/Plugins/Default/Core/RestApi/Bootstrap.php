<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * @category  Shopware
 * @package   ShopwarePlugins\RestApi
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_RestApi_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Controller_Request_RequestHttp
     */
    protected $request;

    /**
     * @var Enlight_Controller_Response_ResponseHttp
     */
    protected $response;

    /**
     * @var bool
     */
    protected $isApiCall = false;

    /**
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent('Enlight_Controller_Front_DispatchLoopStartup', 'onDispatchLoopStartup');
        $this->subscribeEvent('Enlight_Controller_Front_PreDispatch', 'onFrontPreDispatch');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_Auth', 'onInitResourceAuth');

        return true;
    }

    public function assembleRoute($request, $response)
    {
        $path = $request->getPathInfo();

        $path = explode('/', trim($path, '/'));
        $path = array_pad($path, 7, null);

        $type     = $path[1];
        $id       = !empty($path[2]) ? $path[2] : false;
        $subType  = !empty($path[3]) ? $path[3] : false;
        $subId    = is_numeric($path[4]) ? (int) $path[4] : false;

        $method = strtoupper($request->getParam('_method', $request->getMethod()));

        $action = 'invalid';

        if ($method === 'GET' && $id === false) {
            $action = 'index';
            $response->setHttpResponseCode(200);
        } elseif ($method === 'GET') {
            $action = 'get';
            $response->setHttpResponseCode(200);
        } elseif ($method === 'PUT' && $id === false) {
            $action = 'invalid'; // 405 Method Not Allowed
            $response->setHttpResponseCode(405);
        } elseif ($method === 'PUT') {
            $action = 'put';
        } elseif ($method === 'POST') {
            $action = 'post';
            // Set default http status code for successfull request
            $response->setHttpResponseCode(201);
        } elseif ($method === 'DELETE' && $id === false) {
            $action = 'invalid'; // 405 Method Not Allowed
        } elseif ($method === 'DELETE') {
            $response->setHttpResponseCode(200);
            $action = 'delete';
        }

        if ($action == 'invalid') {
            $request->setParam('id', $id);
            $request->setParam('subId', $subId);

            $request->setControllerName('index');
            $request->setActionName($action);

            return;
        }

        if (!$subType) {
            $request->setParam('id', $id);
            $request->setParam('subId', $subId);
            $request->setActionName($action);

            return;
        }

        if ($action == 'get' && $subId === false ) {
            $subAction = $subType . 'Index';
        } else  {
            $subAction = $subType;
        }

        $action = $action . ucfirst($subAction);

        $request->setParam('id', $id);
        $request->setParam('subId', $subId);
        $request->setActionName($action);
    }

    /**
     * Listener method for the Enlight_Controller_Front_DispatchLoopStartup event.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopStartup(Enlight_Controller_EventArgs $args)
    {
        $this->Application()->Loader()->registerNamespace(
            'ShopwarePlugins\\RestApi\\Components',
                __DIR__ . '/Components/'
        );

        $this->request  = $args->getSubject()->Request();
        $this->response = $args->getSubject()->Response();

        if ($this->request->getModuleName() != 'api') {
            return;
        }
        $this->isApiCall = true;

        $this->assembleRoute($this->request, $this->response);
    }

    /**
     * This pre-dispatch event-hook checks permissions
     *
     * @param Enlight_Event_EventArgs $args
     * @throws Enlight_Controller_Exception
     * @return
     */
    public function onFrontPreDispatch(Enlight_Event_EventArgs $args)
    {
        $request  = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if ($request->getModuleName() != 'api') {
            return;
        }


        /** @var $auth Shopware_Components_Auth */
        $auth = Shopware()->Auth();
        $result = $auth->authenticate();

        if (!$result->isValid()) {
            $request->setControllerName('index');
            $request->setActionName('noauth');

            return;
        }

        $identity = $result->getIdentity();

        $db = Shopware()->Db();
        $select = $db->select()
                     ->from('s_core_auth')
                     ->where('username LIKE ?', $identity['username']);

        $user = $db->query($select)->fetchObject();
        if (!empty($user->roleID)) {
            $user->role = Shopware()->Models()->find(
                'Shopware\Models\User\Role',
                $user->roleID
            );
        }
        $auth->getStorage()->write($user);

        $rawBody = $request->getRawBody();

        try {
            $input = Zend_Json::decode($rawBody);
        } catch (Zend_Json_Exception $e) {
            $response->setHttpResponseCode(400);

            $request->setControllerName('index');
            $request->setActionName('invalid');

            return;
        }

        foreach ((array)$input as $key => $value) {
            if ($value !== null) {
               $request->setPost($key, $value);
            }
        }
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
        if (!$this->isApiCall) {
            return;
        }

        $adapter = new Zend_Auth_Adapter_Http(array(
            'accept_schemes'  => 'digest',
            'realm'           => 'Shopware4 REST-API',
            'digest_domains'  => '/',
            'nonce_timeout'  => 3600,
        ));

        $adapter->setDigestResolver(
            new \ShopwarePlugins\RestApi\Components\StaticResolver(
                Shopware()->Models()
            )
        );

        $adapter->setRequest($this->request);
        $adapter->setResponse($this->response);

        $resource = Shopware_Components_Auth::getInstance();
        $storage = new Zend_Auth_Storage_NonPersistent();
        $resource->setBaseAdapter($adapter);
        $resource->addAdapter($adapter);
        $resource->setStorage($storage);

        return $resource;
    }
}
