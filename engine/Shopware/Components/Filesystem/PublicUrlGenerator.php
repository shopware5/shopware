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

namespace Shopware\Components\Filesystem;

use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublicUrlGenerator implements PublicUrlGeneratorInterface
{
    /**
     * @var string
     */
    private $publicUrl;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $shopwareRootDir;

    /**
     * @var string
     */
    private $publicRootDir;

    /**
     * @param string $shopwareRootDir
     * @param string $publicRootDir
     * @param string $publicUrl
     */
    public function __construct(ContainerInterface $container, $shopwareRootDir, $publicRootDir, $publicUrl = null)
    {
        $this->container = $container;
        $this->shopwareRootDir = $shopwareRootDir;
        $this->publicRootDir = $publicRootDir;
        $this->publicUrl = rtrim($publicUrl ?: $this->createPublicUrl(), '/');
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl($path)
    {
        return $this->publicUrl . '/' . $path;
    }

    /**
     * @return string
     */
    private function createPublicUrl()
    {
        $request = $this->container->get('front')->Request();

        if ($request && $request->getHttpHost()) {
            return ($request->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $this->createPublicPath();
        }

        if ($this->container->initialized('shop')) {
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
            return 'https://' . $shop->getHost() . $shop->getBasePath() . '/' . $this->createPublicPath();
        }

        return 'http://' . $shop->getHost() . $shop->getBasePath() . '/' . $this->createPublicPath();
    }

    /**
     * @return string
     */
    private function createPublicPath()
    {
        return ltrim(str_replace($this->shopwareRootDir, '', $this->publicRootDir), '/');
    }
}
