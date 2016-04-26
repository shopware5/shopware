<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Symfony\Component\Validator\Constraint;

class Repeated extends Constraint
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $message;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'RepeatedValidator';
    }
}
