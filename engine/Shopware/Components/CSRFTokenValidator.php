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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\DependencyInjection\Container;

/**
 * Class CSRFTokenValidator
 * @package Shopware\Components
 */
class CSRFTokenValidator implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $tokenName = 'X-CSRF-Token';

    /**
     * @var bool
     */
    private $isEnabledFrontend;

    /**
     * @var bool
     */
    private $isEnabledBackend;

    /**
     * CSRFTokenValidator constructor.
     * @param Container $container
     * @param bool $isEnabledFrontend
     * @param bool $isEnabledBackend
     */
    public function __construct(Container $container, $isEnabledFrontend = true, $isEnabledBackend = true)
    {
        $this->container = $container;
        $this->isEnabledFrontend = (bool) $isEnabledFrontend;
        $this->isEnabledBackend = (bool) $isEnabledBackend;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend' => 'checkBackendTokenValidation',

            'Enlight_Controller_Action_PreDispatch_Frontend' => 'checkFrontendTokenValidation',
            'Enlight_Controller_Action_PreDispatch_Widgets' => 'checkFrontendTokenValidation'
        ];
    }

    /**
     * @param ActionEventArgs $args
     * @throws CSRFTokenValidationException
     */
    public function checkBackendTokenValidation(ActionEventArgs $args)
    {
        if (!$this->isEnabledBackend) {
            return;
        }

        $controller = $args->getSubject();

        if ($this->isWhitelisted($controller)) {
            return;
        }

        $expected = $this->container->get('BackendSession')->offsetGet($this->tokenName);
        $token = $controller->Request()->getHeader($this->tokenName);

        if (empty($token)) {
            $token = $controller->Request()->getParam('__csrf_token');
        }

        if (!hash_equals($expected, $token)) {
            throw new CSRFTokenValidationException("The provided CSRF-Token is invalid. If you're sure that the request should be valid, the called controller action needs to be whitelisted using the CSRFWhitelistAware interface.");
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @throws CSRFTokenValidationException
     */
    public function checkFrontendTokenValidation(\Enlight_Event_EventArgs $args)
    {
        if (!$this->isEnabledFrontend) {
            return;
        }

        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        $request = $controller->Request();
        $response = $controller->Response();

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $this->container->get('session');
        $token = $session->offsetGet('X-CSRF-Token');


        if (!$token) {
            $token = $this->generateToken();
        } else {
            $token = uniqid();
        }

        if ($this->isWhitelisted($controller)) {
            return;
        }

        if ($request->isPost()) {
            $requestToken = $request->getParam('__csrf_token') ? : $request->getHeader('X-CSRF-Token');
            if (!hash_equals($token, $requestToken)) {
                $this->generateToken();
                throw new CSRFTokenValidationException("The provided X-CSRF-Token is invalid. Please go back, reload the page and try again.");
            }
        }
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        $token = Random::getAlphanumericString(30);
        Shopware()->Session()->offsetSet('X-CSRF-Token', $token);
        setcookie('invalidate-xcsrf-token', 1, 0, '/');

        return $token;
    }

    /**
     * @param \Enlight_Controller_Action $controller
     * @return bool
     */
    private function isWhitelisted(\Enlight_Controller_Action $controller)
    {
        if ($controller instanceof CSRFWhitelistAware) {
            $calledAction = strtolower($controller->Request()->getActionName());
            $whitelistedActions = $controller->getWhitelistedCSRFActions();
            $whitelistedActions = array_map('strtolower', $whitelistedActions);

            if (in_array($calledAction, $whitelistedActions)) {
                return true;
            }
        }

        return false;
    }
}
