<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\FormBundle\Constraint;

use Symfony\Component\Form\Form;
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

        /** @var Form $root */
        $root = $this->context->getRoot();

        if (!$root->has($constraint->getField())) {
            $this->setValidation($constraint);
            return;
        }

        $repeated = $root->get($constraint->getField())->getData();

        if ($repeated !== $value) {
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
        $this->context->getRoot()->addError($error);
    }
}
