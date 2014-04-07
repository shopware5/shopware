<?php

namespace Shopware\Hydrator\DBAL;
use Shopware\Struct as Struct;

class Media
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @param Attribute $attributeHydrator
     */
    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

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

        if (!empty($data['attribute'])) {
            $media->addAttribute(
                'media',
                $this->attributeHydrator->hydrate($data['attribute'])
            );
        }

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

        if (!empty($data['imageAttribute'])) {
            $media->addAttribute(
                'image',
                $this->attributeHydrator->hydrate($data['imageAttribute'])
            );
        }



        return $media;
    }

}