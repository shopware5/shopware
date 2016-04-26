<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var null|int
     */
    protected $customerId;

    /**
     * @param null|array $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
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
        return 'UniqueEmailValidator';
    }

    /**
     * @return null|int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }
}
