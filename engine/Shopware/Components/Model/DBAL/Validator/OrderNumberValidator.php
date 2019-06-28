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

namespace Shopware\Components\Model\DBAL\Validator;

use Shopware\Components\Model\DBAL\Constraints\OrderNumber;
use Shopware\Components\OrderNumberValidator\Exception\InvalidOrderNumberException;
use Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrderNumberValidator extends ConstraintValidator
{
    /**
     * @var OrderNumberValidatorInterface
     */
    private $validator;

    public function __construct(OrderNumberValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof OrderNumber) {
            throw new UnexpectedTypeException($constraint, OrderNumber::class);
        }

        // Custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (empty($value)) {
            return;
        }

        if (is_numeric($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        try {
            $this->validator->validate($value);
        } catch (InvalidOrderNumberException $exception) {
            $this->context->buildViolation($exception->getMessage())
                ->addViolation();
        }
    }
}
