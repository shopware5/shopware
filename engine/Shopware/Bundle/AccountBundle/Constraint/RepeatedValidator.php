<?php

namespace Shopware\Bundle\AccountBundle\Constraint;

use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RepeatedValidator extends ConstraintValidator
{
    /**
     * @param string $value
     * @param Repeated|Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Repeated) {
            return;
        }

        $root = $this->context->getRoot();
        $values = $root->getData();

        if (!isset($values[$constraint->getField()])) {
            $this->setValidation($constraint);
            return;
        }

        $second = $values[$constraint->getField()];

        if ($second !== $value) {
            $this->setValidation($constraint);
            return;
        }
    }

    /**
     * @param Repeated $constraint
     */
    private function setValidation(Repeated $constraint)
    {
        $this->context->buildViolation($constraint->getMessage())
            ->atPath($this->context->getPropertyPath())
            ->addViolation();

        $error = new FormError("");
        $error->setOrigin($this->context->getRoot()->get($constraint->getField()));
        $this->context->getRoot()->addError($error);
    }
}
