<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Symfony\Component\Validator\Constraint;

class CustomerEmail extends Constraint
{
    /**
     * @var int
     */
    protected $customerId;

    /**
     * @var int
     */
    protected $accountMode;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getAccountMode()
    {
        return $this->accountMode;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'CustomerEmailValidator';
    }
}
