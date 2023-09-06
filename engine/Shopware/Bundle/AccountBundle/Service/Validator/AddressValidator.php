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

use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressValidator implements AddressValidatorInterface
{
    private ValidatorInterface $validator;

    private Shopware_Components_Config $config;

    private ?ContextualValidatorInterface $validationContext = null;

    public function __construct(
        ValidatorInterface $validator,
        Shopware_Components_Config $config
    ) {
        $this->validator = $validator;
        $this->config = $config;
    }

    /**
     * @throws ValidationException
     */
    public function validate(Address $address)
    {
        $this->validationContext = $this->validator->startContext();

        $additional = $address->getAdditional();
        $customerType = !empty($additional['customer_type']) ? $additional['customer_type'] : null;

        if ($this->config->get('shopSalutationRequired')) {
            $this->validateField('salutation', $address->getSalutation(), [new NotBlank()]);
        }
        $this->validateField('firstname', $address->getFirstname(), [new NotBlank()]);
        $this->validateField('lastname', $address->getLastname(), [new NotBlank()]);
        $this->validateField('street', $address->getStreet(), [new NotBlank()]);
        $this->validateField('zipcode', $address->getZipcode(), [new NotBlank()]);
        $this->validateField('city', $address->getCity(), [new NotBlank()]);
        $this->validateField('country', $address->getCountry() ? $address->getCountry() : null, [new NotBlank()]);
        $this->validateField('phone', $address->getPhone(), $this->getPhoneConstraints());
        $this->validateField('additionalAddressLine1', $address->getAdditionalAddressLine1(), $this->getAdditionalAddressline1Constraints());
        $this->validateField('additionalAddressLine2', $address->getAdditionalAddressLine2(), $this->getAdditionalAddressline2Constraints());

        if ($address->getCountry() && $address->getCountry()->getForceStateInRegistration()) {
            $this->validateField('state', $address->getState(), [new NotBlank()]);
        }

        if ($customerType === Customer::CUSTOMER_TYPE_BUSINESS) {
            $this->validateField('company', $address->getCompany(), [new NotBlank()]);

            if ($this->config->offsetGet('vatcheckrequired')) {
                $this->validateField('vatId', $address->getVatId(), [new NotBlank()]);
            }
        }

        if ($this->validationContext && $this->validationContext->getViolations()->count()) {
            throw new ValidationException($this->validationContext->getViolations());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(Address $address)
    {
        try {
            $this->validate($address);
        } catch (ValidationException $ex) {
            return false;
        }

        return true;
    }

    /**
     * @param string|object|null $value
     * @param Constraint[]       $constraints
     */
    private function validateField(string $property, $value, array $constraints): void
    {
        if ($this->validationContext !== null) {
            $this->validationContext->atPath($property)->validate($value, $constraints);
        }
    }

    /**
     * @return Constraint[]
     */
    private function getPhoneConstraints(): array
    {
        $constraints = [];

        if ($this->config->offsetGet('showphonenumberfield') && $this->config->offsetGet('requirePhoneField')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getAdditionalAddressline1Constraints(): array
    {
        $constraints = [];

        if ($this->config->offsetGet('showAdditionAddressLine1') && $this->config->offsetGet('requireAdditionAddressLine1')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }

    /**
     * @return Constraint[]
     */
    private function getAdditionalAddressline2Constraints(): array
    {
        $constraints = [];

        if ($this->config->offsetGet('showAdditionAddressLine2') && $this->config->offsetGet('requireAdditionAddressLine2')) {
            $constraints[] = new NotBlank(['message' => null]);
        }

        return $constraints;
    }
}
