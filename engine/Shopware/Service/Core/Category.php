<?php

namespace Shopware\Service\Core;

use Shopware\Gateway;
use Shopware\Service;
use Shopware\Struct;

class Category implements Service\Category
{
    /**
     * @var Gateway\Category
     */
    private $categoryGateway;

    /**
     * @param Gateway\Category $categoryGateway
     */
    function __construct(Gateway\Category $categoryGateway)
    {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\Category::get()
     *
     * @param $id
     * @param Struct\Context $context
     * @return Struct\Category
     */
    public function get($id, Struct\Context $context)
    {
        $categories = $this->getList(array($id), $context);
        return array_shift($categories);
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\Category::getList()
     *
     * @param $ids
     * @param Struct\Context $context
     * @return Struct\Category[]
     */
    public function getList($ids, Struct\Context $context)
    {
        return $this->categoryGateway->getList($ids, $context);
    }
}
