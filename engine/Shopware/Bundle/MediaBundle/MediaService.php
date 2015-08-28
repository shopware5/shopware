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

use Shopware\Components\DependencyInjection\Container;

class MediaService implements MediaServiceInterface
{
    /**
     * @var MediaBackendInterface
     */
    private $backend;

    /**
     * @var MediaPathNormalizer
     */
    private $normalizer;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param MediaBackendInterface $backend
     * @param MediaPathNormalizer $normalizer
     * @param Container $container
     */
    public function __construct(MediaBackendInterface $backend, MediaPathNormalizer $normalizer, Container $container)
    {
        $this->backend = $backend;
        $this->normalizer = $normalizer;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        $path = $this->normalizer->get($path);

        return $this->backend->read($path);
    }

    /**
     * @inheritdoc
     */
    public function getUrl($path)
    {
        if ($this->backend->isRealPathFormat($path)) {
            return $path;
        }

        $path = $this->normalizer->get($path);
        $path = $this->backend->toUrlPath($path);

        $mediaUrl = $this->backend->getMediaUrl();
        if (!$mediaUrl) {
            $request = $this->container->get('front')->Request();

            if ($request && $request->getHttpHost() && $request->getBasePath()) {
                $mediaUrl = ($request->isSecure() ? 'https' : 'http') . '://' . $request->getHttpHost() . $request->getBasePath() . "/";
            } else {
                $mediaUrl = $this->container->get('front')->Router()->assemble(['controller' => 'index', 'module' => 'frontend']);
            }
        }

        return $mediaUrl . $path;
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents)
    {
        $path = $this->normalizer->get($path);

        return $this->backend->write($path, $contents);
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        $path = $this->normalizer->get($path);

        return $this->backend->has($path);
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        $path = $this->normalizer->get($path);

        return $this->backend->delete($path);
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        $path = $this->normalizer->get($path);

        return $this->backend->getSize($path);
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newPath)
    {
        $path = $this->normalizer->get($path);
        $newPath = $this->normalizer->get($newPath);

        return $this->backend->rename($path, $newPath);
    }
}
