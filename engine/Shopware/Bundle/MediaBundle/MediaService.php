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

class MediaService implements MediaServiceInterface
{
    /**
     * @var StrategyFilesystem
     */
    private $filesystem;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $mediaUrl;

    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @param FilesystemInterface $filesystem
     * @param StrategyInterface   $strategy
     * @param Container           $container
     * @param string              $mediaUrl
     */
    public function __construct(FilesystemInterface $filesystem, StrategyInterface $strategy, Container $container, string $mediaUrl)
    {
        $this->filesystem = $filesystem;
        $this->strategy = $strategy;
        $this->container = $container;
        $this->mediaUrl = $this->normalizeMediaUrl($mediaUrl);
    }

    public function getFilesystem(): FilesystemInterface
    {
        return $this->filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        if ($this->strategy->isEncoded($path)) {
            return $this->mediaUrl . '/' . ltrim($path, '/');
        }

        $path = $this->strategy->encode($path);

        return $this->mediaUrl . '/' . ltrim($path, '/');
    }

    /**
     * @param string $mediaUrl
     *
     * @return string
     */
    private function normalizeMediaUrl(string $mediaUrl): string
    {
        if (empty($mediaUrl)) {
            $mediaUrl = $this->createFallbackMediaUrl();
        }

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
