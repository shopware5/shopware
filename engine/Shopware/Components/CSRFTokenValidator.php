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
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\DependencyInjection\Container;

/**
 * Class CSRFTokenValidator
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
     *
     * @param Container $container
     * @param bool      $isEnabledFrontend
     * @param bool      $isEnabledBackend
     */
    public function __construct(Container $container, $isEnabledFrontend = true, $isEnabledBackend = true)
    {
        $this->container = $container;
        $this->isEnabledFrontend = (bool) $isEnabledFrontend;
        $this->isEnabledBackend = (bool) $isEnabledBackend;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend' => 'checkBackendTokenValidation',

            'Enlight_Controller_Action_PreDispatch_Frontend' => 'checkFrontendTokenValidation',
            'Enlight_Controller_Action_PreDispatch_Widgets' => 'checkFrontendTokenValidation',
        ];
    }

    /**
     * CSRF protection for backend actions
     *
     * @param ActionEventArgs $args
     *
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
            throw new CSRFTokenValidationException(sprintf('The provided CSRF-Token is invalid. If you\'re sure that the request to path "%s" should be valid, the called controller action needs to be whitelisted using the CSRFWhitelistAware interface.', $controller->Request()->getRequestUri()));
        }
    }

    /**
     * CSRF protection for frontend actions
     *
     * @param \Enlight_Event_EventArgs $args
     *
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

        // do not check internal subrequests
        if ($request->getAttribute('_isSubrequest')) {
            return;
        }

        // skip if the request has already been validated
        if ($request->getAttribute('isValidated')) {
            return;
        }

        // skip whitelisted actions
        if ($this->isWhitelisted($controller)) {
            return;
        }

        if ($request->isPost() && $request->isXmlHttpRequest()) {
            return;
        }

        if ($request->isGet() && !$this->isProtected($controller)) {
            return;
        }

        if (!$this->checkRequest($request)) {
            throw new CSRFTokenValidationException(sprintf('The provided X-CSRF-Token for path "%s" is invalid. Please go back, reload the page and try again.', $request->getRequestUri()));
        }
        // mark request as validated to avoid double validation
        $request->setAttribute('isValidated', true);
    }

    /**
     * Check if the submitted CSRF token matches with the token stored in the cookie or header
     *
     * @param \Enlight_Controller_Request_Request $request
     *
     * @return bool
     */
    private function checkRequest(\Enlight_Controller_Request_Request $request)
    {
        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();
        $token = $request->getCookie('__csrf_token-' . $context->getShop()->getId());
        $requestToken = $request->getParam('__csrf_token') ?: $request->getHeader('X-CSRF-Token');

        return hash_equals($token, $requestToken);
    }

    /**
     * Check if the controller has opted in for CSRF whitelisting and if the
     * called action is on the whitelist
     *
     * @param \Enlight_Controller_Action $controller
     *
     * @return bool
     */
    private function isWhitelisted(\Enlight_Controller_Action $controller)
    {
        if (!($controller instanceof CSRFWhitelistAware)) {
            return false;
        }

        $calledAction = strtolower($controller->Request()->getActionName());
        $calledAction = str_replace('_', '', $calledAction);
        $whitelistedActions = $controller->getWhitelistedCSRFActions();
        $whitelistedActions = array_map('strtolower', $whitelistedActions);

        return in_array($calledAction, $whitelistedActions);
    }

    /**
     * Check if a controller has opted in for CSRF protection and if the called action
     * should be protected
     *
     * @param \Enlight_Controller_Action $controller
     *
     * @return bool
     */
    private function isProtected(\Enlight_Controller_Action $controller)
    {
        if (!($controller instanceof CSRFGetProtectionAware)) {
            return false;
        }

        $calledAction = strtolower($controller->Request()->getActionName());
        $calledAction = str_replace('_', '', $calledAction);
        $protectedActions = $controller->getCSRFProtectedActions();
        $protectedActions = array_map('strtolower', $protectedActions);

        return in_array($calledAction, $protectedActions);
    }
}
