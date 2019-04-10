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

use ShopwarePlugins\RestApi\Components\BasicAuthResolver;
use ShopwarePlugins\RestApi\Components\StaticResolver;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Plugins_Core_RestApi_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Controller_Request_Request
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

    /**
     * @return array
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
     * Is executed after the collection has been added.
     */
    public function afterInit()
    {
        $this->get('loader')->registerNamespace(
            'ShopwarePlugins\\RestApi\\Components',
            __DIR__ . '/Components/'
        );
    }

    /**
     * Listener method for the Enlight_Controller_Front_DispatchLoopStartup event.
     *
     * @param \Enlight_Controller_EventArgs $args
     */
    public function onDispatchLoopStartup(Enlight_Controller_EventArgs $args)
    {
        $this->request = $args->getSubject()->Request();
        $this->response = $args->getSubject()->Response();

        if ($this->request->getModuleName() != 'api') {
            return;
        }

        $this->isApiCall = true;

        $router = new \ShopwarePlugins\RestApi\Components\Router();
        $router->assembleRoute($this->request, $this->response);
    }

    /**
     * This pre-dispatch event-hook checks permissions
     *
     * @param \Enlight_Controller_EventArgs $args
     */
    public function onFrontPreDispatch(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        if ($request->getModuleName() !== 'api') {
            return;
        }

        /** @var Shopware_Components_Auth $auth */
        $auth = $this->get('auth');
        $result = $auth->authenticate();

        if (!$result->isValid()) {
            $request->setControllerName('error');
            $request->setActionName('noauth');

            return;
        }

        $identity = $result->getIdentity();

        $db = $this->get('db');
        $select = $db->select()
                     ->from('s_core_auth')
                     ->where('username LIKE ?', $identity['username']);

        $user = $db->query($select)->fetchObject();
        if (!empty($user->roleID)) {
            $user->role = $this->get('models')->find(
                'Shopware\Models\User\Role',
                $user->roleID
            );
        }
        $auth->getStorage()->write($user);

        $rawBody = $request->getRawBody();

        try {
            if ($rawBody != '') {
                $input = Zend_Json::decode($rawBody);
            } else {
                $input = null;
            }
        } catch (Zend_Json_Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            $request->setControllerName('error');
            $request->setActionName('invalid');

            return;
        }

        if ($input !== null) {
            $request->replacePost($input);
        }
    }

    /**
     * Initiate shopware auth resource
     * database adapter by default
     *
     * @return \Zend_Auth|null
     */
    public function onInitResourceAuth(Enlight_Event_EventArgs $args)
    {
        if (!$this->isApiCall) {
            return;
        }

        $adapter = new Zend_Auth_Adapter_Http([
            'accept_schemes' => 'basic digest',
            'realm' => 'Shopware REST-API',
            'digest_domains' => '/',
            'nonce_timeout' => 3600,
        ]);

        $adapter->setBasicResolver(
            new BasicAuthResolver(
                $this->get('models')
            )
        );

        $adapter->setDigestResolver(
            new StaticResolver(
                $this->get('models')
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
