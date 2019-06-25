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

namespace Shopware\Bundle\MediaBundle;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Util;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaService implements MediaServiceInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var string
     */
    private $mediaUrl;

    /**
     * @var array
     */
    private $config;

    /**
     * @throws \Exception
     */
    public function __construct(FilesystemInterface $filesystem, StrategyInterface $strategy, ContainerInterface $container, array $config)
    {
        $this->filesystem = $filesystem;
        $this->container = $container;
        $this->strategy = $strategy;
        $this->config = $config;

        if (!isset($config['mediaUrl'])) {
            throw new \Exception(sprintf('Please provide a "mediaUrl" in your %s adapter.', $config['type']));
        }

        $mediaUrl = $config['mediaUrl'] ?: $this->createFallbackMediaUrl();
        $this->mediaUrl = rtrim($mediaUrl, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);

        return $this->filesystem->read($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);

        return $this->filesystem->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        if ($this->strategy->isEncoded($path)) {
            return $this->mediaUrl . '/' . ltrim($path, '/');
        }

        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);

        return $this->mediaUrl . '/' . ltrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, $append = false)
    {
        $path = $this->strategy->encode($path);

        if ($append === false && $this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }

        $this->filesystem->put($path, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, $append = false)
    {
        $path = $this->strategy->encode($path);

        if ($append === false && $this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }

        $this->filesystem->putStream($path, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);

        return $this->filesystem->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $path = $this->strategy->encode($path);

        return $this->filesystem->delete($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);

        return $this->filesystem->getSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        $this->migrateFileLive($path);
        $path = $this->strategy->encode($path);
        $newPath = $this->strategy->encode($newPath);

        return $this->filesystem->rename($path, $newPath);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($path)
    {
        return $this->strategy->normalize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterType()
    {
        return $this->config['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function listFiles($directory = '')
    {
        $files = [];
        foreach ($this->filesystem->listContents($directory, true) as $file) {
            if ($file['type'] === 'dir' || strstr($file['path'], '/.') !== false) {
                continue;
            }

            $files[] = $file['path'];
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname)
    {
        return $this->filesystem->createDir($dirname);
    }

    /**
     * Migrates a file to the new strategy if it's not present
     *
     * @internal
     *
     * @param string $path
     */
    public function migrateFile($path)
    {
        if ($this->getAdapterType() !== 'local' || $this->isEncoded($path)) {
            return;
        }

        $normalizedPath = Util::normalizePath($path);

        if (strpos($normalizedPath, 'media/') !== 0) {
            return;
        }

        $encodedPath = $this->strategy->encode($path);

        if ($this->filesystem->has($path) && !$this->filesystem->has($encodedPath)) {
            $this->filesystem->rename($path, $encodedPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encode($path)
    {
        return $this->strategy->encode($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isEncoded($path)
    {
        return $this->strategy->isEncoded($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Generates a mediaUrl based on the request or router
     *
     * @throws \Exception
     *
     * @return string
     */
    private function createFallbackMediaUrl()
    {
        $request = $this->container->get('front')->Request();

        if ($request && $request->getHttpHost()) {
            return ($request->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . $request->getBasePath() . '/';
        }

        if ($this->container->has('shop')) {
            /** @var Shop $shop */
            $shop = $this->container->get('shop');
        } else {
            /** @var Shop $shop */
            $shop = $this->container->get('models')->getRepository(Shop::class)->getActiveDefault();
        }

        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        if ($shop->getSecure()) {
            return 'https://' . $shop->getHost() . $shop->getBasePath() . '/';
        }

        return 'http://' . $shop->getHost() . $shop->getBasePath() . '/';
    }

    /**
     * Used as internal check for the liveMigration config flag.
     *
     * @param string $path
     */
    private function migrateFileLive($path)
    {
        if (!$this->container->getParameter('shopware.cdn.liveMigration')) {
            return;
        }

        $this->migrateFile($path);
    }
}
