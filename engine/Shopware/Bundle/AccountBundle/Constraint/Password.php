<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class Password extends Constraint
{
    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'PasswordValidator';
    }
}
