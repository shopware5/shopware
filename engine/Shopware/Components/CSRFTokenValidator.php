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
use Enlight_Controller_Action;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_Event_EventArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CSRFTokenValidator implements SubscriberInterface
{
    public const CSRF_SESSION_KEY = '__csrf_token-';

    public const CSRF_TOKEN_ARGUMENT = '__csrf_token';

    public const CSRF_TOKEN_HEADER = 'X-CSRF-Token';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $tokenName = self::CSRF_TOKEN_HEADER;

    /**
     * @var bool
     */
    private $isEnabledFrontend;

    /**
     * @var bool
     */
    private $isEnabledBackend;

    private ContextServiceInterface $contextService;

    private Shopware_Components_Config $componentsConfig;

    /**
     * @param bool $isEnabledFrontend
     * @param bool $isEnabledBackend
     */
    public function __construct(
        ContainerInterface $container,
        ContextServiceInterface $contextService,
        Shopware_Components_Config $componentsConfig,
        $isEnabledFrontend = true,
        $isEnabledBackend = true
    ) {
        $this->container = $container;
        $this->isEnabledFrontend = (bool) $isEnabledFrontend;
        $this->isEnabledBackend = (bool) $isEnabledBackend;
        $this->contextService = $contextService;
        $this->componentsConfig = $componentsConfig;
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

        $expected = $this->container->get('backendsession')->offsetGet($this->tokenName);
        $token = $controller->Request()->getHeader($this->tokenName);

        if (empty($token)) {
            $token = $controller->Request()->getParam(self::CSRF_TOKEN_ARGUMENT);
        }

        if (!hash_equals($expected, $token)) {
            throw new CSRFTokenValidationException(sprintf('The provided CSRF-Token is invalid. If you\'re sure that the request to path "%s" should be valid, the called controller action needs to be whitelisted using the CSRFWhitelistAware interface.', $controller->Request()->getRequestUri()));
        }
    }

    /**
     * CSRF protection for frontend actions
     *
     * @throws CSRFTokenValidationException
     */
    public function checkFrontendTokenValidation(Enlight_Event_EventArgs $args)
    {
        if (!$this->isEnabledFrontend) {
            return;
        }

        /** @var Enlight_Controller_Action $controller */
        $controller = $args->getSubject();
        $request = $controller->Request();
        $response = $controller->Response();

        // do not check internal sub-requests or validated requests
        if ($request->getAttribute('_isSubrequest') || $request->getAttribute('isValidated')) {
            return;
        }

        if ($request->isGet() && !$this->isProtected($controller)) {
            return;
        }

        if ($request->isPost() && $request->isXmlHttpRequest()) {
            return;
        }

        // skip whitelisted actions
        if ($this->isWhitelisted($controller)) {
            return;
        }

        if (!$this->checkRequest($request, $response)) {
            $this->regenerateToken($request, $response);
            throw new CSRFTokenValidationException(sprintf('The provided X-CSRF-Token for path "%s" is invalid. Please go back, reload the page and try again.', $request->getRequestUri()));
        }

        // mark request as validated to avoid double validation
        $request->setAttribute('isValidated', true);
    }

    public function regenerateToken(Request $request, Response $response): string
    {
        $shop = $this->contextService->getShopContext()->getShop();
        $name = self::CSRF_SESSION_KEY . $shop->getId();

        if ($shop->getParentId() && $this->componentsConfig->get('shareSessionBetweenLanguageShops')) {
            $name = self::CSRF_SESSION_KEY . $shop->getParentId();
        }

        $token = Random::getAlphanumericString(30);
        $this->container->get('session')->set($name, $token);

        /*
         * Appending a '/' to the $basePath is not strictly necessary, but it is
         * done to all cookie base paths in the
         * `themes/Frontend/Bare/frontend/index/index.tpl` template. It's done
         * here as well for compatibility reasons.
         */
        $response->headers->setCookie(new Cookie(
            $name,
            $token,
            0,
            sprintf('%s/', $shop->getPath() ?: ''),
            null,
            $shop->getSecure(),
            false
        ));

        return $token;
    }

    /**
     * Check if the submitted CSRF token in cookie or header matches with the token stored in the session
     */
    private function checkRequest(Request $request, Response $response): bool
    {
        $shop = $this->contextService->getShopContext()->getShop();
        $name = self::CSRF_SESSION_KEY . $shop->getId();

        if ($shop->getParentId() && $this->componentsConfig->get('shareSessionBetweenLanguageShops')) {
            $name = self::CSRF_SESSION_KEY . $shop->getParentId();
        }

        $token = $this->container->get('session')->get($name);

        if (!\is_string($token)) {
            return false;
        }

        $requestToken = $request->get(self::CSRF_TOKEN_ARGUMENT, $request->headers->get(self::CSRF_TOKEN_HEADER));

        if (!\is_string($requestToken)) {
            return false;
        }

        return hash_equals($token, $requestToken);
    }

    /**
     * Check if the controller has opted in for CSRF whitelisting and if the
     * called action is on the whitelist
     */
    private function isWhitelisted(Enlight_Controller_Action $controller): bool
    {
        if (!($controller instanceof CSRFWhitelistAware)) {
            return false;
        }

        $calledAction = strtolower($controller->Request()->getActionName());
        $calledAction = str_replace('_', '', $calledAction);
        $whitelistedActions = $controller->getWhitelistedCSRFActions();
        $whitelistedActions = array_map('strtolower', $whitelistedActions);

        return \in_array($calledAction, $whitelistedActions);
    }

    /**
     * Check if a controller has opted in for CSRF protection and if the called action
     * should be protected
     */
    private function isProtected(Enlight_Controller_Action $controller): bool
    {
        if (!($controller instanceof CSRFGetProtectionAware)) {
            return false;
        }

        $calledAction = strtolower($controller->Request()->getActionName());
        $calledAction = str_replace('_', '', $calledAction);
        $protectedActions = $controller->getCSRFProtectedActions();
        $protectedActions = array_map('strtolower', $protectedActions);

        return \in_array($calledAction, $protectedActions);
    }
}
