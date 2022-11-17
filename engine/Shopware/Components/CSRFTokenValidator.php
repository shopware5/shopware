<?php

declare(strict_types=1);
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

class CSRFTokenValidator implements SubscriberInterface
{
    public const CSRF_KEY = '__csrf_token-';

    public const CSRF_TOKEN_ARGUMENT = '__csrf_token';

    public const CSRF_TOKEN_HEADER = 'X-CSRF-Token';
    public const CSRF_TOKEN_RESPONSE_HEADER = 'x-csrf-token';

    private const CSRF_WAS_VALIDATED = 'isValidated';

    private ContainerInterface $container;

    private bool $isEnabledFrontend;

    private bool $isEnabledBackend;

    private ContextServiceInterface $contextService;

    private Shopware_Components_Config $componentsConfig;

    public function __construct(
        ContainerInterface $container,
        ContextServiceInterface $contextService,
        Shopware_Components_Config $componentsConfig,
        bool $isEnabledFrontend = true,
        bool $isEnabledBackend = true
    ) {
        $this->container = $container;
        $this->isEnabledFrontend = $isEnabledFrontend;
        $this->isEnabledBackend = $isEnabledBackend;
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

        $expected = $this->container->get('backendsession')->offsetGet(self::CSRF_TOKEN_HEADER);
        if (!\is_string($expected)) {
            throw new CSRFTokenValidationException('The backend session does not contain a valid CSRF token');
        }
        $token = $controller->Request()->getHeader(self::CSRF_TOKEN_HEADER);

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

        // do not check internal sub-requests or validated requests
        if ($request->getAttribute('_isSubrequest') || $request->getAttribute(self::CSRF_WAS_VALIDATED)) {
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

        if (!$this->checkRequest($request)) {
            throw new CSRFTokenValidationException(sprintf('The provided X-CSRF-Token for path "%s" is invalid. Please go back, reload the page and try again.', $request->getRequestUri()));
        }

        // mark request as validated to avoid double validation
        $request->setAttribute(self::CSRF_WAS_VALIDATED, true);
    }

    public function clearExistingCookie(): void
    {
        $shop = $this->contextService->getShopContext()->getShop();
        $name = $this->getCsrfName();

        $response = $this->container->get('front')->Response();
        $response->headers->setCookie(new Cookie(
            $name,
            Random::getAlphanumericString(30),
            0,
            sprintf('%s/', $shop->getPath() ?: ''),
            '',
            $shop->getSecure(),
            false
        ));
    }

    /**
     * Check if the submitted CSRF token matches with the token stored in the cookie or header
     */
    private function checkRequest(Request $request): bool
    {
        $name = $this->getCsrfName();

        $token = $request->cookies->get($name);

        if (!\is_string($token)) {
            return false;
        }

        $requestToken = $request->get(self::CSRF_TOKEN_ARGUMENT, $request->headers->get(self::CSRF_TOKEN_HEADER));

        if (!\is_string($requestToken)) {
            return false;
        }

        return hash_equals($token, $requestToken);
    }

    private function getCsrfName(): string
    {
        $shop = $this->contextService->getShopContext()->getShop();

        $name = self::CSRF_KEY . $shop->getId();

        if ($shop->getParentId() && $this->componentsConfig->get('shareSessionBetweenLanguageShops')) {
            $name = self::CSRF_KEY . $shop->getParentId();
        }

        return $name;
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

        return \in_array($calledAction, $whitelistedActions, true);
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

        return \in_array($calledAction, $protectedActions, true);
    }
}
