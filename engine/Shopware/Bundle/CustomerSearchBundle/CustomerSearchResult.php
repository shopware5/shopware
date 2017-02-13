<?php

namespace Shopware\Bundle\CustomerSearchBundle;

class CustomerSearchResult
{
    /**
     * @var int
     */
    protected $total;

    /**
     * @var array[]
     */
    protected $customers;

    /**
     * @param int $total
     * @param \array[] $customers
     */
    public function __construct($total, array $customers)
    {
        $this->total = $total;
        $this->customers = $customers;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return array[]
     */
    public function getCustomers()
    {
        return $this->customers;
    }
}
