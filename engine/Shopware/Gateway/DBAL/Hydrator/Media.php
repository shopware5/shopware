<?php

namespace Shopware\Gateway\DBAL\Hydrator;
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

        if (!empty($data['__attribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__attribute_', $data)
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

        $media->setPreview(($data['__image_main'] == 1));

        if (!empty($data['__imageAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__imageAttribute_', $data)
            );

            $media->addAttribute('image', $attribute);
        }

        return $media;
    }

    private function extractFields($prefix, $data)
    {
        $result = array();
        foreach($data as $field => $value) {
            if (strpos($field, $prefix) === 0) {
                $key = str_replace($prefix, '', $field);
                $result[$key] = $value;
            }
        }
        return $result;
    }

}