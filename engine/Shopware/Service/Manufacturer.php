<?php

namespace Shopware\Service;

use Shopware\Struct;
use Shopware\Gateway\DBAL as Gateway;

class Manufacturer
{
    /**
     * @var \Shopware\Gateway\DBAL\Manufacturer
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
     * @param array $ids
     * @param Struct\Context $context
     * @return Struct\Product\Manufacturer[]
     */
    public function getList(array $ids, Struct\Context $context)
    {
        $manufacturers = $this->manufacturerGateway->getList($ids, $context);

        return $manufacturers;
    }
}
