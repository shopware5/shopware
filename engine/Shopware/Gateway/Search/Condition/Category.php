<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Category extends Condition
{
    /**
     * @var array
     */
    private $categoryIds;

    /**
     * @param $categoryIds
     */
    function __construct(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'category';
    }
}
