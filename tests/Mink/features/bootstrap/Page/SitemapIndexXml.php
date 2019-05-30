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

namespace Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class SitemapIndexXml extends Page
{
    /**
     * @var string
     */
    protected $path = '/sitemap_index.xml';

    /**
     * @throws \Exception
     */
    public function checkXml(array $links)
    {
        $homepageUrl = rtrim($this->getParameter('base_url'), '/');
        $xml = json_decode(json_encode(simplexml_load_string($this->getContent())), true);

        if (!isset($xml['sitemap']['loc'])) {
            Helper::throwException('Sitemap is missing in /sitemap_index.xml');
        }

        $expected = sprintf('%s/web/sitemap/shop-1/%s', $homepageUrl, $links[0]['name']);
        if ($xml['sitemap']['loc'] !== $expected) {
            Helper::throwException(sprintf('Sitemap url does not match excepted, excepted: %s, given %s', $expected, $xml['sitemap']['loc']));
        }
    }
}
