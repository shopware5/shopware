<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Download extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * Creates a new Struct\Product\Download struct with the passed data.
     *
     * @param array $data
     * @return \Shopware\Struct\Product\Download
     */
    public function hydrate(array $data)
    {
        $download = new Struct\Product\Download();

        $download->setId((int)$data['__download_id']);

        $download->setDescription($data['__download_description']);

        $download->setFile($data['__download_filename']);

        $download->setSize((float)$data['__download_size']);

        if (!empty($data['__downloadAttribute_id'])) {
            $attribute = $this->extractFields('__downloadAttribute_', $data);
            $download->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $download;
    }
}