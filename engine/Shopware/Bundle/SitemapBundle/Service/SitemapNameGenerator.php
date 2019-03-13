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

use League\Flysystem\FilesystemInterface;
use Shopware\Bundle\SitemapBundle\SitemapNameGeneratorInterface;

class SitemapNameGenerator implements SitemapNameGeneratorInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct(FilesystemInterface $filesystem, $pattern = 'sitemap-{number}.xml.gz')
    {
        $this->pattern = $pattern;
        $this->filesystem = $filesystem;
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
            $path = 'shop-' . $shopId . '/' . str_ireplace(
                ['{number}'],
                [$number], $this->pattern);
            ++$number;
        } while ($this->filesystem->has($path));

        return $path;
    }
}
