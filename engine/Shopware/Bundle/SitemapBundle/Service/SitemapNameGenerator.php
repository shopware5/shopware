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

namespace Shopware\Bundle\SitemapBundle\Service;

use Shopware\Bundle\SitemapBundle\SitemapNameGeneratorInterface;

class SitemapNameGenerator implements SitemapNameGeneratorInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $path
     * @param string $pattern
     */
    public function __construct($path, $pattern = 'sitemap-shop-{shopId}-{number}.xml.gz')
    {
        $this->path = $path;
        $this->pattern = $pattern;
    }

    /**
     * @param int $shopId
     *
     * @return string
     */
    public function getSitemapFilename($shopId)
    {
        $number = 1;
        do {
            $filename = str_ireplace(
                ['{shopId}', '{number}'],
                [$shopId, $number], $this->pattern);

            $path = sprintf('%s/%s', rtrim($this->path, '/'), $filename);
            ++$number;
        } while (file_exists($path));

        return $path;
    }

    /**
     * @param int $shopId
     *
     * @return string
     */
    public function getSitemapFilenameGlob($shopId)
    {
        return str_ireplace(['{shopId}', '{number}'], [$shopId, '*'], $this->pattern);
    }
}
