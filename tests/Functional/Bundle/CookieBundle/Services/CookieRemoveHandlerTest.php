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

namespace Shopware\Tests\Functional\Bundle\CookieBundle\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\Services\CookieCollector;
use Shopware\Bundle\CookieBundle\Services\CookieRemoveHandler;
use Symfony\Component\HttpFoundation\Cookie;

class CookieRemoveHandlerTest extends TestCase
{
    public function testRemoveCookiesFromPreferencesRemovesAllNonTechnicallyRequiredCookiesNoPreferences(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $response->headers->setCookie(new Cookie('foo', 'not_required'));
        $response->headers->setCookie(new Cookie('session-1', 'required'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertCount(1, $response->headers->getCookies());
    }

    public function testRemoveCookiesFromPreferencesRemovesUnknownCookies(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'comfort' => [
                        'name' => 'comfort',
                        'cookies' => [
                            'unknown' => [
                                'name' => 'unknown',
                                'active' => true,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $response->headers->setCookie(new Cookie('unknown', 'foo'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        // Preferences cookie is "overwritten" here due to the unknown cookie - that's why we still count one cookie
        static::assertCount(1, $response->headers->getCookies());
        static::assertSame(CookieRemoveHandler::PREFERENCES_COOKIE_NAME, $response->headers->getCookies()[0]->getName());
    }

    public function testRemoveCookiesFromPreferencesRemovesCookieInactiveNotRequired(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'comfort' => [
                        'name' => 'comfort',
                        'cookies' => [
                            'sUniqueID' => [
                                'name' => 'sUniqueID',
                                'active' => false,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $response->headers->setCookie(new Cookie('sUniqueID', 'foo'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertEmpty($response->headers->getCookies());
    }

    public function testRemoveCookiesFromPreferencesDoesNotRemoveCookieInactiveButRequired(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'technical' => [
                        'name' => 'technical',
                        'cookies' => [
                            'currency' => [
                                'name' => 'currency',
                                'active' => false,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $response->headers->setCookie(new Cookie('currency', 'foo'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertCount(1, $response->headers->getCookies());
        static::assertSame('currency', $response->headers->getCookies()[0]->getName());
    }

    public function testRemoveCookiesFromPreferencesDoesNotRemoveCookieActive(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'comfort' => [
                        'name' => 'comfort',
                        'cookies' => [
                            'sUniqueID' => [
                                'name' => 'sUniqueID',
                                'active' => true,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $response->headers->setCookie(new Cookie('sUniqueID', 'foo'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertCount(1, $response->headers->getCookies());
        static::assertSame('sUniqueID', $response->headers->getCookies()[0]->getName());
    }

    public function testRemoveCookiesFromPreferencesRemovesNewCookies(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'comfort' => [
                        'name' => 'comfort',
                        'cookies' => [
                            'sUniqueID' => [
                                'name' => 'sUniqueID',
                                'active' => false,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $response->headers->setCookie(new Cookie('sUniqueID', 'foo'));

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertEmpty($response->headers->getCookies());
    }

    public function testRemoveCookiesFromPreferencesRemovesCookiesFromBrowser(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie(
            CookieRemoveHandler::PREFERENCES_COOKIE_NAME,
            json_encode([
                'groups' => [
                    'comfort' => [
                        'name' => 'comfort',
                        'cookies' => [
                            'sUniqueID' => [
                                'name' => 'sUniqueID',
                                'active' => false,
                            ],
                        ],
                    ],
                ],
            ])
        );

        $request->setCookie('sUniqueID', 'foo');

        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertCount(1, $response->headers->getCookies());
        static::assertSame('sUniqueID', $response->headers->getCookies()[0]->getName());
        static::assertSame(0, $response->headers->getCookies()[0]->getExpiresTime());
    }

    public function testRemoveCookiesFromPreferencesRemovesCookiesFromBrowserForAllPaths(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setCookie('foo', 'bar');
        $request->setBasePath('/base_path');
        $request->setBaseUrl('/virtual_url');
        $request->setPathInfo('/path/');
        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        static::assertCount(7, $response->headers->getCookies());
    }

    public function testRemoveCookiesFromPreferencesRemovesNotYetSetCookiesFromAllPaths(): void
    {
        $cookieRemoveHandler = $this->getRemoveHandler();

        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setBasePath('');
        $request->setPathInfo('/path/');
        $request->setBaseUrl('/virtual_url');
        $response->headers->setCookie(new Cookie('sUniqueID', 'foo', 0, '/'));
        $response->headers->setCookie(new Cookie('sUniqueID', 'foo', 0, '/path'));
        $response->headers->setCookie(new Cookie('sUniqueID', 'foo', 0, '/path/'));
        $response->headers->setCookie(new Cookie('sUniqueID', 'foo', 0, '/virtual_url'));
        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);
        static::assertEmpty($response->headers->getCookies());
    }

    private function getRequest(): \Enlight_Controller_Request_RequestTestCase
    {
        return new \Enlight_Controller_Request_RequestTestCase();
    }

    private function getResponse(): \Enlight_Controller_Response_ResponseTestCase
    {
        return new \Enlight_Controller_Response_ResponseTestCase();
    }

    private function getRemoveHandler(): CookieRemoveHandler
    {
        return new CookieRemoveHandler(
            Shopware()->Container()->get(CookieCollector::class)->collect()
        );
    }
}
