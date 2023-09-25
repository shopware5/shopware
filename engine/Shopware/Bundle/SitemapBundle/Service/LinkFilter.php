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

namespace Shopware\Bundle\SitemapBundle\Service;

class LinkFilter
{
    /**
     * Helper function to filter predefined links, which should not be in the sitemap (external links, sitemap links itself)
     * Returns false, if the link is not allowed
     *
     * @param array<string, mixed> $userParams
     */
    public static function filterLink(?string $link, array &$userParams = []): bool
    {
        if (empty($link)) {
            return true;
        }
        $parsedUserParams = (string) parse_url($link, PHP_URL_QUERY);
        parse_str($parsedUserParams, $userParams);
        $blacklist = ['', 'sitemap', 'sitemapXml'];
        if (\in_array($userParams['sViewport'] ?? [], $blacklist, true)) {
            return false;
        }

        return true;
    }
}
