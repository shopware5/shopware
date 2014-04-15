<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Category extends Facet
{
    /**
     * @var array
     */
    public $categories;

    public function getName()
    {
        return 'category';
    }

}