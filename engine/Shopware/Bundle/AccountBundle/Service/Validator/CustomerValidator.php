<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\AccountBundle\Service\Validator;

use Shopware\Bundle\AccountBundle\Constraint\CustomerEmail;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerValidator implements CustomerValidatorInterface
{
    private ValidatorInterface $validator;

    private ContextServiceInterface $context;

    private Shopware_Components_Config $config;

    private ContextualValidatorInterface $validationContext;

    public function __construct(
        ValidatorInterface $validator,
        ContextServiceInterface $context,
        Shopware_Components_Config $config
    ) {
        $this->validator = $validator;
        $this->context = $context;
        $this->config = $config;
    }

    /**
     * @throws ValidationException
     */
    public function validate(Customer $customer)
    {
        $this->validationContext = $this->validator->startContext();

        $this->validateField('firstname', $customer->getFirstname(), [new NotBlank()]);
        $this->validateField('lastname', $customer->getLastname(), [new NotBlank()]);

        $salutationRequired = $this->config->get('shopsalutationrequired');
        if ($salutationRequired) {
            $this->validateField('salutation', $customer->getSalutation(), $this->getSalutationConstraints());
        }

        $this->validateField('email', $customer->getEmail(), [
            new CustomerEmail([
                'shop' => $this->context->getShopContext()->getShop(),
                'customerId' => $customer->getId(),
                'accountMode' => $customer->getAccountMode(),
            ]),
        ]);

        if ($this->validationContext->getViolations()->count() > 0) {
            throw new ValidationException($this->validationContext->getViolations());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Customer $customer)
    {
        try {
            $this->validate($customer);
        } catch (ValidationException $ex) {
            return false;
        }

        return true;
    }

    /**
     * @param Constraint[] $constraints
     */
    private function validateField(string $property, ?string $value, array $constraints): void
    {
        $this->validationContext->atPath($property)->validate($value, $constraints);
    }

    /**
     * @return Constraint[]
     */
    private function getSalutationConstraints(): array
    {
        $salutations = explode(',', $this->config->get('shopsalutations'));

        return [new Choice(['choices' => $salutations])];
    }
}
