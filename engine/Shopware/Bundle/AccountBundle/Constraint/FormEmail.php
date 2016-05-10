<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Symfony\Component\Validator\Constraint;

class FormEmail extends Constraint
{
    /**
     * @var Shop
     */
    protected $shop;

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
        return 'FormEmailValidator';
    }
}
