<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class Manufacturer implements Service\Manufacturer
{
    /**
     * @var Gateway\Manufacturer
     */
    private $manufacturerGateway;

    /**
     * @param Gateway\Manufacturer $manufacturerGateway
     */
    function __construct(Gateway\Manufacturer $manufacturerGateway)
    {
        $this->manufacturerGateway = $manufacturerGateway;
    }

    /**
     * @inheritdoc
     */
    public function get($id, Struct\Context $context)
    {
        $manufacturers = $this->getList(array($id), $context);
        return $manufacturers;
    }

    /**
     * @inheritdoc
     */
    public function getList(array $ids, Struct\Context $context)
    {
        $manufacturers = $this->manufacturerGateway->getList($ids, $context);

        return $manufacturers;
    }
}
