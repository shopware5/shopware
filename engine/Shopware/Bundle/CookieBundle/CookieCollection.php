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

namespace Shopware\Bundle\CookieBundle;

use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieCollection extends ArrayCollection implements JsonSerializable
{
    public function isValid(): bool
    {
        foreach ($this as $cookieStruct) {
            if (!$cookieStruct instanceof CookieStruct) {
                return false;
            }
        }

        return true;
    }

    public function hasCookieWithName(string $cookieName): bool
    {
        return $this->exists(static function (string $key, CookieStruct $cookieStruct) use ($cookieName) {
            return preg_match($cookieStruct->getMatchingPattern(), $cookieName) === 1;
        });
    }

    public function getCookieByName(string $cookieName): ?CookieStruct
    {
        /** @var CookieStruct $cookieStruct */
        foreach ($this as $cookieStruct) {
            if (!preg_match($cookieStruct->getMatchingPattern(), $cookieName)) {
                continue;
            }

            return $cookieStruct;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
