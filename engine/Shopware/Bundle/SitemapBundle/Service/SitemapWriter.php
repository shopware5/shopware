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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Bundle\SitemapBundle\Exception\UnknownFileException;
use Shopware\Bundle\SitemapBundle\SitemapWriterInterface;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Models\Shop\Shop;

class SitemapWriter implements SitemapWriterInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var SitemapNameGenerator
     */
    private $sitemapNameGenerator;

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array<Sitemap[]>
     */
    private $sitemaps = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SitemapNameGenerator $sitemapNameGenerator,
        FilesystemInterface $filesystem,
        LoggerInterface $logger = null
    ) {
        $this->sitemapNameGenerator = $sitemapNameGenerator;
        $this->filesystem = $filesystem;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param Url[] $urls
     *
     * @return bool
     */
    public function writeFile(Shop $shop, array $urls = [])
    {
        if (empty($urls)) {
            return false;
        }

        $this->openFile($shop->getId());

        foreach ($urls as $url) {
            if ($this->files[$shop->getId()]['urlCount'] >= self::SITEMAP_URL_LIMIT) {
                $this->closeFile($shop->getId());

                $this->openFile($shop->getId());
            }

            ++$this->files[$shop->getId()]['urlCount'];
            $this->write($this->files[$shop->getId()]['fileHandle'], (string) $url);
        }
    }

    /**
     * Closes open file handles and moves sitemaps to their target location.
     *
     * @throws UnknownFileException
     */
    public function closeFiles()
    {
        foreach ($this->files as $shopId => $params) {
            $this->closeFile($shopId);
        }

        $this->moveFiles();
    }

    /**
     * @param int $shopId
     *
     * @throws UnknownFileException
     *
     * @return bool
     */
    private function closeFile($shopId)
    {
        if (!array_key_exists($shopId, $this->files)) {
            throw new UnknownFileException(sprintf('No open file "%s"', $shopId));
        }

        $fileHandle = $this->files[$shopId]['fileHandle'];
        $this->write($fileHandle, '</urlset>');

        gzclose($fileHandle);

        if (!array_key_exists($shopId, $this->sitemaps)) {
            $this->sitemaps[$shopId] = [];
        }

        $this->sitemaps[$shopId][] = new Sitemap(
            $this->files[$shopId]['fileName'],
            $this->files[$shopId]['urlCount']
        );

        unset($this->files[$shopId]);

        return true;
    }

    /**
     * @param int $shopId
     *
     * @return bool
     */
    private function openFile($shopId)
    {
        if (array_key_exists($shopId, $this->files)) {
            return true;
        }

        $filePath = sprintf(
            '%s/sitemap-shop-%d-%s.xml.gz',
            rtrim(sys_get_temp_dir(), '/'),
            $shopId,
            microtime(true) * 10000
        );

        $fileHandler = gzopen($filePath, 'wb');

        if (!$fileHandler) {
            $this->logger->error(sprintf('Could not generate sitemap file, unable to write to "%s"', $filePath));

            return false;
        }

        $this->files[$shopId] = [
            'fileHandle' => $fileHandler,
            'fileName' => $filePath,
            'urlCount' => 0,
        ];

        $this->write($fileHandler, '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        return true;
    }

    /**
     * @param resource $fileHandler
     * @param string   $content
     */
    private function write($fileHandler, $content)
    {
        gzwrite($fileHandler, $content);
    }

    /**
     * Makes sure all files get closed and replaces the old sitemaps with the freshly generated ones
     */
    private function moveFiles()
    {
        /** @var Sitemap[] $sitemaps */
        foreach ($this->sitemaps as $shopId => $sitemaps) {
            // Delete old sitemaps for this siteId
            foreach ($this->filesystem->listContents(sprintf('shop-%d', $shopId)) as $file) {
                $this->filesystem->delete($file['path']);
            }

            // Move new sitemaps into place
            foreach ($sitemaps as $sitemap) {
                $sitemapFileName = $this->sitemapNameGenerator->getSitemapFilename($shopId);
                try {
                    $this->filesystem->write($sitemapFileName, file_get_contents($sitemap->getFilename()));
                } catch (\League\Flysystem\Exception $exception) {
                    // If we could not move the file to it's target, we remove it here to not clutter tmp dir
                    unlink($sitemap->getFilename());

                    $this->logger->error(sprintf('Could not move sitemap to "%s" in the location for sitemaps', $sitemapFileName));
                }
            }
        }
    }
}
