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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Manufacturer::get()
     *
     * @param $id
     * @param Struct\Context $context
     * @return Struct\Product\Manufacturer[]
     */
    public function get($id, Struct\Context $context)
    {
        $manufacturers = $this->getList(array($id), $context);
        return $manufacturers;
    }

    /**
     * @see \Shopware\Service\Manufacturer::get()
     *
     * @param array $ids
     * @param Struct\Context $context
     * @return Struct\Product\Manufacturer[] Indexed by the manufacturer id
     */
    public function getList(array $ids, Struct\Context $context)
    {
        $manufacturers = $this->manufacturerGateway->getList($ids, $context);

        return $manufacturers;
    }
}
