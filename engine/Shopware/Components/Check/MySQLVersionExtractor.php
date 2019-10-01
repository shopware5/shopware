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

namespace Shopware\Components\Check;

class MySQLVersionExtractor
{
    public static function extract(string $versionString): array
    {
        if (stripos($versionString, 'mariadb') === false) {
            if (strpos($versionString, '-')) {
                $versionString = substr($versionString, 0, strpos($versionString, '-'));
            }

            return ['mysql', $versionString];
        }

        return ['mariadb', self::getVersionNumber($versionString)];
    }

    private static function getVersionNumber(string $versionString): string
    {
        if (!preg_match(
            '/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i',
            $versionString,
            $versionParts
        )) {
            throw new \RuntimeException(sprintf('Invalid version string: %s', $versionString));
        }

        return $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];
    }
}
