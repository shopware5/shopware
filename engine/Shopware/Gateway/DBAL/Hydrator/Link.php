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

        $link->setId((int)$data['__link_id']);

        $link->setDescription($data['__link_description']);

        $link->setLink($data['__link_link']);

        $link->setTarget($data['__link_target']);

        if (!empty($data['__linkAttribute_id'])) {
            $attribute = $this->extractFields('__linkAttribute_', $data);
            $link->addAttribute('core', $this->attributeHydrator->hydrate($attribute));
        }

        return $link;
    }
}