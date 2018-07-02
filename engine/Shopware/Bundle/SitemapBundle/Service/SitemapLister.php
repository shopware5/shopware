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

use Shopware\Bundle\SitemapBundle\SitemapListerInterface;
use Shopware\Bundle\SitemapBundle\SitemapNameGeneratorInterface;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;

/**
 * Reads a list of files from the sitemap directory and returns structs describing those files.
 */
class SitemapLister implements SitemapListerInterface
{
    /**
     * @var string
     */
    private $sitemapDirectory;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var SitemapNameGeneratorInterface
     */
    private $nameGenerator;

    /**
     * @param string                        $sitemapDirectory
     * @param string                        $projectDirectory
     * @param SitemapNameGeneratorInterface $nameGenerator
     */
    public function __construct($sitemapDirectory, $projectDirectory, SitemapNameGeneratorInterface $nameGenerator)
    {
        $this->sitemapDirectory = $sitemapDirectory;
        $this->projectDirectory = $projectDirectory;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSitemaps($shopId = null)
    {
        $iterator = new \DirectoryIterator($this->sitemapDirectory);

        if ($shopId) {
            $dir = rtrim($this->sitemapDirectory, DIRECTORY_SEPARATOR);
            $iterator = new \GlobIterator($dir . DIRECTORY_SEPARATOR . $this->nameGenerator->getSitemapFilenameGlob($shopId));
        }

        $sitemaps = [];

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getBasename()[0] === '.') {
                continue;
            }

            $sitemaps[] = new Sitemap(
                str_replace($this->projectDirectory, '', $this->sitemapDirectory) . $file->getFilename(),
                0,
                new \DateTime('@' . $file->getCTime())
            );
        }

        return $sitemaps;
    }
}
