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

namespace Shopware\Bundle\AccountBundle\Service\Validator;

use Shopware\Bundle\AccountBundle\Constraint\UniqueEmail;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerValidator
 * @package Shopware\Bundle\AccountBundle\Service\Validator
 */
class CustomerValidator implements CustomerValidatorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * CustomerValidator constructor.
     * @param ValidatorInterface $validator
     * @param ContextServiceInterface $context
     * @param \Shopware_Components_Config $config
     */
    public function __construct(
        ValidatorInterface $validator,
        ContextServiceInterface $context,
        \Shopware_Components_Config $config
    ) {
        $this->validator = $validator;
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * @param Customer $customer
     * @throws ValidationException
     */
    public function validate(Customer $customer)
    {
        $this->validateField($customer->getFirstname(), [new NotBlank()]);
        $this->validateField($customer->getLastname(), [new NotBlank()]);
        $this->validateField($customer->getSalutation(), [new NotBlank(), new Choice(['choices' => ['mr', 'ms']])]);
        $this->validateField($customer->getEmail(), $this->getEmailConstraints($customer->getId()));
        $this->validateField($customer->getBirthday(), $this->getBirthdayConstraints());
    }

    /**
     * @param mixed $value
     * @param ConstraintValidatorInterface[] $constraints
     * @throws ValidationException
     */
    private function validateField($value, $constraints)
    {
        $violations = $this->validator->validate($value, $constraints);
        if ($violations->count()) {
            throw new ValidationException($violations);
        }
    }

    /**
     * @param int|null $customerId
     * @return \Symfony\Component\Validator\ConstraintValidatorInterface[]
     */
    private function getEmailConstraints($customerId = null)
    {
        return [
            new NotBlank(),
            new Email(),
            new UniqueEmail([
                'shop' => $this->context->getShopContext()->getShop(),
                'customerId' => $customerId
            ])
        ];
    }

    /**
     * @return ConstraintValidatorInterface[]
     */
    private function getBirthdayConstraints()
    {
        $birthdayConstraints = [new Date()];

        if ($this->config->get('showBirthdayField') && $this->config->get('requireBirthdayField')) {
            $birthdayConstraints[] = new NotBlank();
        }

        return $birthdayConstraints;
    }
}
