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

namespace Shopware\Components\Auth\Validator;

use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Auth\Constraint\UserEmail;
use Shopware\Components\Auth\Constraint\UserName;
use Shopware\Models\User\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidator implements UserValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ContextualValidatorInterface
     */
    private $validationContext;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(User $user)
    {
        $this->validationContext = $this->validator->startContext();

        $this->validateField('username', $user->getUsername(), [
            new UserName([
                'userId' => $user->getId(),
            ]),
        ]);
        $this->validateField('name', $user->getName(), [new NotBlank()]);
        $this->validateField('role', $user->getRole(), [new NotBlank()]);
        $this->validateField('email', $user->getEmail(), [
            new UserEmail([
                'userId' => $user->getId(),
            ]),
        ]);

        if ($this->validationContext->getViolations()->count()) {
            throw new ValidationException($this->validationContext->getViolations());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(User $user)
    {
        try {
            $this->validate($user);
        } catch (ValidationException $ex) {
            return false;
        }

        return true;
    }

    /**
     * @param string       $property
     * @param Constraint[] $constraints
     */
    private function validateField($property, $value, $constraints)
    {
        $this->validationContext->atPath($property)->validate($value, $constraints);
    }
}
