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
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Services\CookieCollector;
use Shopware\Bundle\CookieBundle\Services\CookieHandler;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieHandlerTest extends TestCase
{
    public function testGetCookies(): void
    {
        $cookieHandler = $this->getCookieHandler();

        $cookieGroupsCollection = $cookieHandler->getCookies();

        static::assertSame(5, $cookieGroupsCollection->count());
    }

    public function testGetTechnicallyRequiredCookies(): void
    {
        $cookieHandler = $this->getCookieHandler();

        $cookieCollection = $cookieHandler->getTechnicallyRequiredCookies();

        static::assertSame(10, $cookieCollection->count());
    }

    public function testIsCookieAllowedByPreferencesReturnsTrueIsAllowed(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('sUniqueID', [
            'groups' => [
                [
                    'name' => 'comfort',
                    'cookies' => [
                        'statistic' => [
                            'name' => 'sUniqueID',
                            'active' => true,
                        ],
                    ],
                ],
            ],
        ]);

        static::assertTrue($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsFalseIsNotAllowed(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('sUniqueID', [
            'groups' => [
                [
                    'name' => 'comfort',
                    'cookies' => [
                        'statistic' => [
                            'name' => 'sUniqueID',
                            'active' => false,
                        ],
                    ],
                ],
            ],
        ]);

        static::assertFalse($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsFalseUnknownCookieNotTechnicallyRequired(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('statistic', []);

        static::assertFalse($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsTrueUnknownCookieButTechnicallyRequired(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('session-2', []);

        static::assertTrue($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsFalseUnknownCookie(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('bar', []);

        static::assertFalse($result);
    }

    public function testIsCookieAllowedByPreferencesWorksWithRegex(): void
    {
        Shopware()->Container()->get('events')->addListener(
            'CookieCollector_Collect_Cookies',
            [RegexCookieSubscriber::class, 'addCookie']
        );

        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('bar-ABC', [
            'groups' => [
                [
                    'name' => 'comfort',
                    'cookies' => [
                        'statistic' => [
                            'name' => 'bar',
                            'active' => true,
                        ],
                    ],
                ],
            ],
        ]);

        static::assertTrue($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsTrueValidRegexSecondElement(): void
    {
        Shopware()->Container()->get('events')->addListener(
            'CookieCollector_Collect_Cookies',
            [RegexCookieSubscriber::class, 'addCookie']
        );

        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('bar-ABC', [
            'groups' => [
                [
                    'name' => 'comfort',
                    'cookies' => [
                        'statistic' => [
                            'name' => 'bar1',
                            'active' => false,
                        ],
                        'statistic2' => [
                            'name' => 'bar',
                            'active' => true,
                        ],
                        'statistic3' => [
                            'name' => 'bar3',
                            'active' => false,
                        ],
                    ],
                ],
            ],
        ]);

        static::assertTrue($result);
    }

    public function testIsCookieAllowedByPreferencesReturnsTrueTechnicallyRequiredButInactive(): void
    {
        $cookieHandler = $this->getCookieHandler();
        $result = $cookieHandler->isCookieAllowedByPreferences('session-1', [
            'groups' => [
                [
                    'name' => 'technical',
                    'cookies' => [
                        'session' => [
                            'name' => 'session',
                            'active' => false,
                        ],
                    ],
                ],
            ],
        ]);

        static::assertTrue($result);
    }

    private function getCookieHandler(): CookieHandler
    {
        return new CookieHandler(
            Shopware()->Container()->get(CookieCollector::class)->collect()
        );
    }
}

class RegexCookieSubscriber
{
    public function addCookie(): CookieCollection
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct(
            'bar',
            '/^bar\-[A-Za-z]{3}$/',
            'bar',
            CookieGroupStruct::PERSONALIZATION
        ));

        return $cookieCollection;
    }
}
