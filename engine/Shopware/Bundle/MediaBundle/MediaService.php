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
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\Shop;

/**
 * Class MediaService
 */
class MediaService implements MediaServiceInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var Container
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
     * @param FilesystemInterface $filesystem
     * @param StrategyInterface   $strategy
     * @param Container           $container
     * @param string              $mediaUrl
     */
    public function __construct(FilesystemInterface $filesystem, StrategyInterface $strategy, Container $container, string $mediaUrl)
    {
        $this->filesystem = $filesystem;
        $this->container = $container;
        $this->strategy = $strategy;
        $this->mediaUrl = $this->normalizeMediaUrl($mediaUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $path = $this->strategy->encode($path);

        return $this->filesystem->read($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
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
        $path = $this->strategy->encode($path);

        return $this->filesystem->getSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
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
    public function listFiles($directory = '')
    {
        $files = [];
        foreach ($this->filesystem->listContents($directory, true) as $file) {
            if ($file['type'] == 'dir' || strstr($file['path'], '/.') !== false) {
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
    public function getAdapter()
    {
        return $this->filesystem;
    }

    public function getAdapterType()
    {
        return '';
    }

    /**
     * @param string $mediaUrl
     *
     * @return string
     */
    private function normalizeMediaUrl(string $mediaUrl): string
    {
        $mediaUrl = !empty($mediaUrl) ?: $this->createFallbackMediaUrl();
        $mediaUrl = rtrim($mediaUrl, '/');

        return $mediaUrl;
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
            return ($request->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . $request->getBasePath() . '/web/';
        }

        if ($this->container->has('Shop')) {
            /** @var Shop $shop */
            $shop = $this->container->get('Shop');
        } else {
            /** @var Shop $shop */
            $shop = $this->container->get('models')->getRepository(Shop::class)->getActiveDefault();
        }

        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        if ($shop->getSecure()) {
            return 'https://' . $shop->getHost() . $shop->getBasePath() . '/web/';
        }

        return 'http://' . $shop->getHost() . $shop->getBasePath() . '/web/';
    }
}
