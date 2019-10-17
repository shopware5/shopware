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

namespace Shopware\Components\Privacy;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Request_RequestHttp as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Services\CookieHandler;
use Shopware\Bundle\CookieBundle\Services\CookieHandlerInterface;
use Shopware_Components_Config as Config;
use Symfony\Component\HttpFoundation\Cookie;

class CookieRemoveSubscriber implements SubscriberInterface
{
    const COOKIE_MODE_NOTICE = 0;
    const COOKIE_MODE_TECHNICAL = 1;
    const COOKIE_MODE_ALL = 2;

    /**
     * @var bool
     */
    private $cookieRemovalActive;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CookieHandlerInterface
     */
    private $cookieHandler;

    public function __construct(Config $config, CookieHandlerInterface $cookieHandler)
    {
        $this->cookieRemovalActive = $config->get('cookie_note_mode') && $config->get('show_cookie_note');
        $this->config = $config;
        $this->cookieHandler = $cookieHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Widgets' => 'onPostDispatch',
        ];
    }

    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args): void
    {
        if (!$this->cookieRemovalActive) {
            return;
        }

        $controller = $args->getSubject();

        $allowCookie = (int) $controller->Request()->getCookie('allowCookie');

        if ($this->config->get('cookie_note_mode') === self::COOKIE_MODE_ALL) {
            if ($allowCookie === 1) {
                return;
            }

            header_remove('Set-Cookie');

            $this->removeAllCookies($controller->Request(), $controller->Response());

            return;
        }

        if ($this->config->get('cookie_note_mode') === self::COOKIE_MODE_TECHNICAL) {
            $controller->View()->assign(
                'cookieGroups',
                $this->convertToArray($this->cookieHandler->getCookies())
            );

            if ($allowCookie === 1) {
                return;
            }

            $this->removeCookiesFromPreferences($controller->Request(), $controller->Response());
        }
    }

    private function removeCookiesFromPreferences(Request $request, Response $response): void
    {
        $preferences = $request->getCookie(CookieHandler::PREFERENCES_COOKIE_NAME);

        if ($preferences === null) {
            $this->removeAllCookies($request, $response);

            return;
        }

        $preferences = json_decode($preferences, true);

        $this->removeCookies($request, $response, function (string $cookieName) use ($preferences) {
            return $this->cookieHandler->isCookieAllowedByPreferences($cookieName, $preferences);
        });
    }

    private function removeAllCookies(Request $request, Response $response): void
    {
        $technicallyRequiredCookies = $this->cookieHandler->getTechnicallyRequiredCookies();

        $this->removeCookies($request, $response, static function (string $cookieKey) use ($technicallyRequiredCookies) {
            return $technicallyRequiredCookies->hasCookieWithName($cookieKey);
        });
    }

    private function removeCookies(Request $request, Response $response, callable $validationFunction): void
    {
        $requestCookies = $request->getCookie();
        $cookieBasePath = $request->getBasePath();

        $cookiePath = $cookieBasePath . '/';
        $currentPath = $cookieBasePath . $request->getPathInfo();
        $currentPathWithoutSlash = trim($currentPath, '/');

        foreach ($response->getCookies() as $responseCookie) {
            if (!$validationFunction($responseCookie['name'])) {
                if (array_key_exists($responseCookie['name'], $requestCookies)) {
                    continue;
                }

                $response->headers->removeCookie($responseCookie['name']);
                $response->headers->removeCookie($responseCookie['name'], $cookieBasePath);
                $response->headers->removeCookie($responseCookie['name'], $cookiePath);
                $response->headers->removeCookie($responseCookie['name'], $currentPath);
                $response->headers->removeCookie($responseCookie['name'], $currentPathWithoutSlash);
            }
        }

        foreach ($requestCookies as $cookieKey => $cookieName) {
            if (!$validationFunction($cookieKey)) {
                $response->headers->setCookie(new Cookie($cookieKey, null, 0));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $cookieBasePath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $cookiePath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $currentPath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $currentPathWithoutSlash));
            }
        }
    }

    private function convertToArray(CookieGroupCollection $cookieGroupCollection): array
    {
        return json_decode(json_encode($cookieGroupCollection), true);
    }
}
