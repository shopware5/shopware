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

use Shopware\Bundle\SitemapBundle\Exception\UnknownFileException;
use Shopware\Bundle\SitemapBundle\SitemapWriterInterface;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Models\Shop\Shop;

class SitemapWriter implements SitemapWriterInterface
{
    /**
     * @var string
     */
    private $sitemapDir;

    /**
     * @var string
     */
    private $tempPath;

    /**
     * @var SitemapNameGenerator
     */
    private $sitemapNameGenerator;

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var Sitemap[]
     */
    private $sitemaps = [];

    /**
     * @param SitemapNameGenerator $sitemapNameGenerator
     * @param string               $sitemapDir
     * @param string               $tempPath
     */
    public function __construct(SitemapNameGenerator $sitemapNameGenerator, $sitemapDir, $tempPath = '/temp')
    {
        $this->sitemapNameGenerator = $sitemapNameGenerator;
        $this->sitemapDir = $sitemapDir;
        $this->tempPath = $tempPath;
    }

    /**
     * Makes sure all files get closed and replaces the old sitemaps with the freshly generated ones
     */
    public function __destruct()
    {
        /** @var Sitemap[] $sitemaps */
        foreach ($this->sitemaps as $shopId => $sitemaps) {
            try {
                $this->closeFile($shopId);
            } catch (UnknownFileException $ex) {
                // Ok, got closed already
            }

            // Delete old sitemaps for this siteId
            array_map('unlink', glob(rtrim($this->sitemapDir, '/') . '/sitemap-shop-' . $shopId . '-*.xml.gz'));

            // Move new Sitemaps into place
            foreach ($sitemaps as $sitemap) {
                rename($sitemap->getFilename(), $this->sitemapNameGenerator->getSitemapFilename($shopId));
            }
        }
    }

    /**
     * @param Shop  $shop
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
            if ($this->files[$shop->getId()]['urlCount'] >= 49999) {
                $this->closeFile($shop->getId());

                $this->openFile($shop->getId());
            }

            ++$this->files[$shop->getId()]['urlCount'];
            $this->write($this->files[$shop->getId()]['fileHandle'], (string) $url);
        }
    }

    public function closeFiles()
    {
        foreach ($this->files as $shopId => $params) {
            $this->closeFile($shopId);
        }
    }

    /**
     * @param int $shopId
     *
     * @throws \Shopware\Bundle\SitemapBundle\Exception\UnknownFileException
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
            rtrim($this->tempPath, '/'),
            $shopId,
            microtime(true) * 10000
        );

        $fileHandler = gzopen($filePath, 'wb');

        if (!$fileHandler) {
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
}
