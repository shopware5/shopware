<?php

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

class CustomerGroupReader extends GenericReader
{
    protected function getIdentifierField()
    {
        return 'entity.key';
    }
}