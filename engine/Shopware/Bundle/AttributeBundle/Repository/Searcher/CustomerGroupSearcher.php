<?php

namespace Shopware\Bundle\AttributeBundle\Repository\Searcher;

class CustomerGroupSearcher extends GenericSearcher
{
    protected function getIdentifierField()
    {
        return 'entity.key';
    }
}