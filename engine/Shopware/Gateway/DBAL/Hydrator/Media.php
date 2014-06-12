<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models;

class Media extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @var Manager
     */
    private $thumbnailManager;

    /**
     * @param Attribute $attributeHydrator
     * @param \Shopware\Components\Thumbnail\Manager $thumbnailManager
     */
    function __construct(Attribute $attributeHydrator, Manager $thumbnailManager)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->thumbnailManager = $thumbnailManager;
    }

    /**
     * @param array $data
     * @return Struct\Media
     */
    public function hydrate(array $data)
    {
        $media = new Struct\Media();

        if (isset($data['__media_id'])) {
            $media->setId($data['__media_id']);
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
            $media->setFile($data['__media_path']);
        }

        if ($media->getType() == Models\Media\Media::TYPE_IMAGE
            && $data['__mediaSettings_create_thumbnails']) {

            $media->setThumbnails(
                $this->getMediaThumbnails($data)
            );
        }

        if (!empty($data['__mediaAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__mediaAttribute_', $data)
            );
            $media->addAttribute('media', $attribute);
        }

        return $media;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Media
     */
    public function hydrateProductImage(array $data)
    {
        $media = $this->hydrate($data);

        $data = array_merge($data, $this->getImageTranslation($data));

        $media->setName($data['__image_description']);

        $media->setPreview((bool) ($data['__image_main'] == 1));

        if (!empty($data['__imageAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__imageAttribute_', $data)
            );

            $media->addAttribute('image', $attribute);
        }

        return $media;
    }


    /**
     * @param array $data Contains the array data for the media
     * @return array
     */
    private function getMediaThumbnails(array $data)
    {
        $sizes = explode(';', $data['__mediaSettings_thumbnail_size']);

        $entity = new Models\Media\Media();
        $entity->fromArray(array(
            'type' => $data['__media_type'],
            'name' => $data['__media_name'],
            'extension' => $data['__media_extension']
        ));

        return $this->thumbnailManager->getMediaThumbnails(
            $entity,
            $sizes
        );
    }

    private function getImageTranslation($data)
    {
        if (!isset($data['__image_translation'])
            || empty($data['__image_translation'])) {

            return array();
        }

        $translation = unserialize($data['__image_translation']);

        if (empty($translation)) {
            return array();
        }

        return array(
            '__image_description' => $translation['description']
        );
    }
}
