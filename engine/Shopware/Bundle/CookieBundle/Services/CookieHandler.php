<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\CookieBundle\Services;

use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Exceptions\NoCookieGroupByNameKnownException;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;

class CookieHandler implements CookieHandlerInterface
{
    public const PREFERENCES_COOKIE_NAME = 'cookiePreferences';
    public const XDEBUG_COOKIE_NAME = 'XDEBUG_SESSION';

    /**
     * @var CookieGroupCollection
     */
    protected $cookieGroupCollection;

    public function __construct(CookieGroupCollection $cookieGroupCollection)
    {
        $this->cookieGroupCollection = $cookieGroupCollection;
    }

    public function getCookies(): CookieGroupCollection
    {
        return $this->cookieGroupCollection;
    }

    public function getTechnicallyRequiredCookies(): CookieCollection
    {
        return $this->cookieGroupCollection->getGroupByName(CookieGroupStruct::TECHNICAL)->getCookies();
    }

    public function isCookieAllowedByPreferences(string $cookieName, array $preferences): bool
    {
        // XDebug Cookie
        if ($cookieName === self::XDEBUG_COOKIE_NAME) {
            return true;
        }

        $foundCookie = $this->getCookies()->matchCookieByName($cookieName);

        if (!$foundCookie) {
            return false;
        }

        foreach ($preferences['groups'] ?? [] as $cookieGroup) {
            foreach ($cookieGroup['cookies'] as $cookie) {
                if ($cookie['name'] !== $foundCookie->getName()) {
                    continue;
                }

                try {
                    $cookieGroupStruct = $this->cookieGroupCollection->getGroupByName($cookieGroup['name']);
                } catch (NoCookieGroupByNameKnownException $e) {
                    continue;
                }

                return $cookieGroupStruct->isRequired() ?: $cookie['active'];
            }
        }

        return $this->getTechnicallyRequiredCookies()->hasCookieWithName($cookieName);
    }
}
