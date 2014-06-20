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
     * @inheritdoc
     */
    public function get($id, Struct\Context $context)
    {
        $categories = $this->getList(array($id), $context);
        return array_shift($categories);
    }

    /**
     * @inheritdoc
     */
    public function getList($ids, Struct\Context $context)
    {
        return $this->categoryGateway->getList($ids, $context);
    }
}
