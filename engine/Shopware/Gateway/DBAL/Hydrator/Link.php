<?php

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct as Struct;

class Link extends Hydrator
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
     * @return \Shopware\Struct\Product\Link
     */
    public function hydrate(array $data)
    {
        $link = new Struct\Product\Link();

        $link->setId((int)$data['id']);

        $link->setDescription($data['description']);

        $link->setLink($data['filename']);

        $link->setTarget($data['size']);

        if (!empty($data['__linkAttribute_id'])) {
            $attribute = $this->extractFields('__linkAttribute_', $data);
            $link->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $link;
    }
}