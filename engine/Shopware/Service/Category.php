<?php

namespace Shopware\Service;
use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class Category
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
     * @param $ids
     * @param Struct\Context $context
     */
    public function getList($ids, Struct\Context $context)
    {
        return $this->categoryGateway->getList($ids, $context);
    }
}