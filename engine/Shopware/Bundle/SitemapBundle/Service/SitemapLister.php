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
use Shopware\Bundle\SitemapBundle\SitemapListerInterface;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;
use Shopware\Components\Filesystem\PublicUrlGeneratorInterface;

/**
 * Reads a list of files from the sitemap directory and returns structs describing those files.
 */
class SitemapLister implements SitemapListerInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var PublicUrlGeneratorInterface
     */
    private $publicUrlGenerator;

    public function __construct(FilesystemInterface $filesystem, PublicUrlGeneratorInterface $publicUrlGenerator)
    {
        $this->filesystem = $filesystem;
        $this->publicUrlGenerator = $publicUrlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSitemaps($shopId)
    {
        $files = $this->filesystem->listContents('shop-' . $shopId);
        $sitemaps = [];

        foreach ($files as $file) {
            if ($file['basename'][0] === '.') {
                continue;
            }

            $path = $this->publicUrlGenerator->generateUrl('sitemap/shop-' . $shopId . '/' . $file['basename']);

            $sitemaps[] = new Sitemap(
                $path,
                0,
                new \DateTime('@' . $file['timestamp'])
            );
        }

        return $sitemaps;
    }
}
