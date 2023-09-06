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

namespace Shopware\Tests\Mink\Page\Frontend\Sitemap;

use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;

class SitemapIndexXml extends Page
{
    /**
     * @var string
     */
    protected $path = '/sitemap_index.xml';

    /**
     * @throws Exception
     */
    public function checkXml(array $links)
    {
        $homepageUrl = rtrim($this->getParameter('base_url') ?? '', '/');
        $xml = json_decode(json_encode(simplexml_load_string($this->getContent())), true);
        $xml = $xml['body']['div'][0]['sitemapindex'];

        if (!isset($xml['sitemap']['loc'])) {
            Helper::throwException('Sitemap is missing in /sitemap_index.xml');
        }

        $expected = sprintf('/web/sitemap/shop-1/%s', $links[0]['name']);
        if (!str_contains($xml['sitemap']['loc'], $expected)) {
            Helper::throwException(sprintf('Sitemap url does not match excepted, excepted: %s, given %s', $expected, $xml['sitemap']['loc']));
        }
    }
}
