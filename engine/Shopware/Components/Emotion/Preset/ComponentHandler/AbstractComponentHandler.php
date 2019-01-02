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

namespace Shopware\Components\Emotion\Preset\ComponentHandler;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\Resource\Media as MediaResource;
use Shopware\Models\Media\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractComponentHandler implements ComponentHandlerInterface
{
    /**
     * @var MediaResource
     */
    protected $mediaResource;

    /**
     * @var MediaServiceInterface
     */
    protected $mediaService;

    /**
     * @param MediaServiceInterface $mediaService
     * @param MediaResource         $mediaResource
     * @param ContainerInterface    $container
     */
    public function __construct(MediaServiceInterface $mediaService, MediaResource $mediaResource, ContainerInterface $container)
    {
        $this->mediaService = $mediaService;
        $this->mediaResource = $mediaResource;
        $this->mediaResource->setContainer($container);
        $this->mediaResource->setManager($container->get('models'));
    }

    /**
     * @param string $assetPath
     * @param int    $albumId
     *
     * @return Media
     */
    protected function doAssetImport($assetPath, $albumId = -3)
    {
        $media = $this->mediaResource->internalCreateMediaByFileLink($assetPath, $albumId);

        if ($media) {
            $this->mediaResource->getManager()->flush($media);
        }

        return $media;
    }

    /**
     * @param int $id
     *
     * @return null|object
     */
    protected function getMediaById($id)
    {
        return $this->mediaResource->getRepository()->find($id);
    }

    /**
     * @param string $path
     *
     * @return null|object
     */
    protected function getMediaByPath($path)
    {
        return $this->mediaResource->getRepository()->findOneBy(['path' => $path]);
    }
}
