<?php declare(strict_types=1);
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

namespace Shopware\Bundle\CookieBundle\Services;

use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieRemoveHandler extends CookieHandler implements CookieRemoveHandlerInterface
{
    public const COOKIE_CONFIG_KEY = 'x-shopware-cookie-config';
    public const COOKIE_GROUP_COLLECTION_KEY = 'x-shopware-cookie-group-collection';

    public function removeCookiesFromPreferences(Request $request, Response $response): void
    {
        $preferences = $request->cookies->get(self::PREFERENCES_COOKIE_NAME);

        if ($preferences === null) {
            $this->removeAllCookies($request, $response);

            return;
        }

        $preferences = json_decode($preferences, true);

        $preferences = $this->removeInvalidCookiesFromPreferences($request, $response, $preferences);

        $this->removeCookies($request, $response, function (string $cookieName) use ($preferences) {
            return $this->isCookieAllowedByPreferences($cookieName, $preferences);
        });
    }

    public function removeAllCookies(Request $request, Response $response): void
    {
        $technicallyRequiredCookies = $this->getTechnicallyRequiredCookies();

        $this->removeCookies($request, $response, static function (string $cookieKey) use ($technicallyRequiredCookies) {
            return $technicallyRequiredCookies->hasCookieWithName($cookieKey);
        });
    }

    protected function removeCookies(Request $request, Response $response, callable $validationFunction): void
    {
        $requestCookies = $request->cookies->all();
        $cookieBasePath = $request->getBasePath();

        $cookiePath = $cookieBasePath . '/';
        $currentPath = $cookieBasePath . $request->getPathInfo();
        $currentPathWithoutSlash = '/' . trim($currentPath, '/');
        foreach ($response->headers->getCookies() as $responseCookie) {
            $cookieName = $responseCookie->getName();

            if (!$validationFunction($cookieName)) {
                if (array_key_exists($cookieName, $requestCookies)) {
                    continue;
                }

                $response->headers->removeCookie($cookieName);
                $response->headers->removeCookie($cookieName, $cookieBasePath);
                $response->headers->removeCookie($cookieName, $cookiePath);
                $response->headers->removeCookie($cookieName, $currentPath);
                $response->headers->removeCookie($cookieName, $currentPathWithoutSlash);
                $response->headers->removeCookie($cookieName, $request->getBaseUrl());
                $response->headers->removeCookie($cookieName, $cookieBasePath . $request->getBaseUrl());
            }
        }

        foreach ($requestCookies as $cookieKey => $cookieName) {
            if (!$validationFunction($cookieKey)) {
                $response->headers->setCookie(new Cookie($cookieKey, null, 0));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $cookieBasePath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $cookiePath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $currentPath));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $currentPathWithoutSlash));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $request->getBaseUrl()));
                $response->headers->setCookie(new Cookie($cookieKey, null, 0, $cookieBasePath . $request->getBaseUrl()));
            }
        }
    }

    protected function removeInvalidCookiesFromPreferences(Request $request, Response $response, array $preferences): array
    {
        $allowedCookies = $this->cookieGroupCollection;

        foreach ($preferences['groups'] as $group) {
            foreach ($group['cookies'] as $cookie) {
                $cookieCollection = $allowedCookies->getGroupByName($group['name'])->getCookies();

                if ($this->hasCookieWithTechnicalName($cookieCollection, $cookie['name'])) {
                    continue;
                }

                unset($preferences['groups'][$group['name']]['cookies'][$cookie['name']]);
                $this->setNewPreferencesCookie($request, $response, $preferences);
            }
        }

        return $preferences;
    }

    protected function hasCookieWithTechnicalName(CookieCollection $cookieCollection, string $technicalName): bool
    {
        return $cookieCollection->exists(static function (string $key, CookieStruct $cookieStruct) use ($technicalName) {
            return $cookieStruct->getName() === $technicalName;
        });
    }

    protected function setNewPreferencesCookie(Request $request, Response $response, array $preferences): void
    {
        $expire = new \DateTime();
        $expire->modify('+180 day');

        $response->headers->setCookie(
            new Cookie(self::PREFERENCES_COOKIE_NAME, json_encode($preferences), $expire, $request->getBasePath() . '/', null, false, false, true)
        );
    }
}
