<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Property extends Condition
{
    /**
     * @var array
     */
    private $valueIds = array();

    /**
     * @param array $valueIds
     */
    function __construct(array $valueIds)
    {
        $this->valueIds = $valueIds;
    }

    public function getName()
    {
        return 'property';
    }

    /**
     * @return array
     */
    public function getValueIds()
    {
        return $this->valueIds;
    }


}