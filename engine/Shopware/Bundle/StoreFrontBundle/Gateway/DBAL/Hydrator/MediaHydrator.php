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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Components\Thumbnail\Manager;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var Manager
     */
    private $thumbnailManager;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var Connection
     */
    private $database;

    /**
     * @param AttributeHydrator                      $attributeHydrator
     * @param \Shopware\Components\Thumbnail\Manager $thumbnailManager
     * @param MediaService                           $mediaService
     * @param Connection                             $database
     */
    public function __construct(AttributeHydrator $attributeHydrator, Manager $thumbnailManager, MediaService $mediaService, Connection $database)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->thumbnailManager = $thumbnailManager;
        $this->mediaService = $mediaService;
        $this->database = $database;
    }

    /**
     * @param array $data
     *
     * @return Struct\Media
     */
    public function hydrate(array $data)
    {
        $media = new Struct\Media();

        $translation = $this->getTranslation($data, '__media');
        $data = array_merge($data, $translation);

        if (isset($data['__media_id'])) {
            $media->setId((int) $data['__media_id']);
        }

        if (isset($data['__media_name'])) {
            $media->setName($data['__media_name']);
        }

        if (isset($data['__media_description'])) {
            $media->setDescription($data['__media_description']);
        }

        if (isset($data['__media_type'])) {
            $media->setType($data['__media_type']);
        }

        if (isset($data['__media_extension'])) {
            $media->setExtension($data['__media_extension']);
        }

        if (isset($data['__media_path'])) {
            $media->setPath($data['__media_path']);
            $media->setFile($this->mediaService->getUrl($data['__media_path']));
        }

        /*
         * Live Migration to add width/height to images
         */
        if ($this->isUpdateRequired($media, $data)) {
            $data = $this->updateMedia($data);
        }

        if (isset($data['__media_width'])) {
            $media->setWidth((int) $data['__media_width']);
        }

        if (isset($data['__media_height'])) {
            $media->setHeight((int) $data['__media_height']);
        }

        if ($media->getType() == Struct\Media::TYPE_IMAGE
            && $data['__mediaSettings_create_thumbnails']) {
            $media->setThumbnails(
                $this->getMediaThumbnails($data)
            );
        }

        if (!empty($data['__mediaAttribute_id'])) {
            $this->attributeHydrator->addAttribute($media, $data, 'mediaAttribute', 'media');
        }

        return $media;
    }

    /**
     * @param array $data
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Media
     */
    public function hydrateProductImage(array $data)
    {
        $media = $this->hydrate($data);

        $translation = $this->getTranslation($data, '__image');
        $data = array_merge($data, $translation);

        // For legacy reasons we check if the __image_description can be used
        if (!empty($data['__image_description'])) {
            $media->setName($data['__image_description']);
        }

        if (!$media->getName() && $media->getDescription()) {
            $media->setName($media->getDescription());
        }

        $media->setPreview((bool) ($data['__image_main'] == 1));

        if (!empty($data['__imageAttribute_id'])) {
            $this->attributeHydrator->addAttribute($media, $data, 'imageAttribute', 'image', 'image');
        }

        return $media;
    }

    /**
     * @param Struct\Media $media
     * @param array        $data
     *
     * @return bool
     */
    private function isUpdateRequired(Struct\Media $media, array $data)
    {
        if ($media->getType() != Struct\Media::TYPE_IMAGE) {
            return false;
        }
        if (!array_key_exists('__media_width', $data)) {
            return false;
        }
        if (!array_key_exists('__media_height', $data)) {
            return false;
        }
        if ($data['__media_width'] !== null && $data['__media_height'] !== null) {
            return false;
        }

        return $this->mediaService->getFilesystem()->has($data['__media_path']);
    }

    /**
     * @param array $data Contains the array data for the media
     *
     * @return array
     */
    private function getMediaThumbnails(array $data)
    {
        $thumbnailData = $this->thumbnailManager->getMediaThumbnails(
            $data['__media_name'],
            $data['__media_type'],
            $data['__media_extension'],
            explode(';', $data['__mediaSettings_thumbnail_size'])
        );

        $thumbnails = [];
        foreach ($thumbnailData as $row) {
            $retina = $row['retinaSource'];

            if (!$data['__mediaSettings_thumbnail_high_dpi']) {
                $retina = null;
            }

            if (!empty($retina)) {
                $retina = $this->mediaService->getUrl($retina);
            }

            $thumbnails[] = new Struct\Thumbnail(
                $this->mediaService->getUrl($row['source']),
                $retina,
                $row['maxWidth'],
                $row['maxHeight']
            );
        }

        return $thumbnails;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function updateMedia(array $data)
    {
        list($width, $height) = getimagesizefromstring($this->mediaService->getFilesystem()->read($data['__media_path']));
        $this->database->executeUpdate(
            'UPDATE s_media SET width = :width, height = :height WHERE id = :id',
            [
                ':width' => $width,
                ':height' => $height,
                ':id' => $data['__media_id'],
            ]
        );

        $data['__media_width'] = $width;
        $data['__media_height'] = $height;

        return $data;
    }
}
