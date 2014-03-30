<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Media
{
    /**
     * @param array $data
     * @return \Shopware\Struct\Media
     */
    public function hydrateProductImage(array $data)
    {
        $media = new Struct\Media();

        $mediaData = $data['media'];

        $media->setId($mediaData['id']);

        $media->setName($mediaData['name']);

        $media->setPreview(($data['main'] == 1));

        $media->setDescription($mediaData['description']);

        $media->setType($mediaData['type']);

        $media->setExtension($mediaData['extension']);

        $media->setFile($mediaData['path']);

        return $media;
    }
}