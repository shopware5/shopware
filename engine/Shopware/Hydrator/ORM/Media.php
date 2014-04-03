<?php

namespace Shopware\Hydrator\ORM;
use Shopware\Struct as Struct;

class Media
{
    /**
     * @param array $data
     * @return Struct\Media
     */
    public function hydrate(array $data)
    {
        $media = new Struct\Media();

        $media->setId($data['id']);

        $media->setName($data['name']);

        $media->setDescription($data['description']);

        $media->setType($data['type']);

        $media->setExtension($data['extension']);

        $media->setFile($data['path']);

        $media->setThumbnails($data['thumbnails']);

        return $media;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Media
     */
    public function hydrateProductImage(array $data)
    {
        $media = $this->hydrate($data['media']);

        $media->setPreview(($data['main'] == 1));

        return $media;
    }
}