<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class ShippingFree implements Facet
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var bool
     */
    private $filtered = false;

    /**
     * @return string
     */
    public function getName()
    {
        return 'shipping_free';
    }

    /**
     * @param $filtered
     */
    public function setIsFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->filtered;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }


}