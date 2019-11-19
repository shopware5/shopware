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

namespace Shopware\Bundle\CookieBundle;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\CookieBundle\Exceptions\NoCookieGroupByNameKnownException;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieGroupCollection extends ArrayCollection implements \JsonSerializable
{
    public function isValid(): bool
    {
        foreach ($this as $cookieGroupStruct) {
            if (!$cookieGroupStruct instanceof CookieGroupStruct) {
                return false;
            }
        }

        return true;
    }

    public function matchCookieByName(string $cookieName): ?CookieStruct
    {
        /** @var CookieGroupStruct $cookieGroup */
        foreach ($this as $cookieGroup) {
            if (!$foundCookie = $cookieGroup->getCookies()->getCookieByName($cookieName)) {
                continue;
            }

            return $foundCookie;
        }

        return null;
    }

    public function getGroupByName(string $groupName): CookieGroupStruct
    {
        $cookieGroupCollection = $this->filter(static function (CookieGroupStruct $cookieGroupStruct) use ($groupName) {
            return $cookieGroupStruct->getName() === $groupName;
        });

        if ($cookieGroupCollection->count() === 0) {
            throw new NoCookieGroupByNameKnownException(sprintf('There is no known cookie group with name %s', $groupName));
        }

        return $cookieGroupCollection->first();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
