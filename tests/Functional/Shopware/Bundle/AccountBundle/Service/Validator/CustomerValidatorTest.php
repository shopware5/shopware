<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Shopware\Bundle\AccountBundle\Service\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\Validator\CustomerValidator;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerValidatorTest extends TestCase
{
    private const CUSTOMER_FIRSTNAME = 'Foo';
    private const CUSTOMER_LASTNAME = 'Bar';
    private const CUSTOMER_EMAIL = 'foo@bar.com';

    public function testIsValidChecksIfValidationIsRequired(): void
    {
        $salutation = 'mr';

        $violationsMock = $this->createConstraintViolationListMock();

        $validatorMock = $this->createValidatorMock($violationsMock, $salutation, 4);

        $customerValidator = new CustomerValidator($validatorMock, $this->createContextMock(), $this->createConfigMock(true));

        $customerValidator->isValid($this->createCustomer($salutation));
    }

    public function testIsValidChecksIfValidationIsNotRequired(): void
    {
        $salutation = 'mrs';

        $violationsMock = $this->createConstraintViolationListMock();

        $validatorMock = $this->createValidatorMock($violationsMock, $salutation, 3);

        $customerValidator = new CustomerValidator($validatorMock, $this->createContextMock(), $this->createConfigMock(false));

        $customerValidator->isValid($this->createCustomer($salutation));
    }

    /**
     * @return ConstraintViolationListInterface&MockObject
     */
    public function createConstraintViolationListMock(): MockObject
    {
        $violationsMock = $this->createMock(ConstraintViolationListInterface::class);
        $violationsMock->method('count')->willReturn(0);

        return $violationsMock;
    }

    /**
     * @param ConstraintViolationListInterface&MockObject $violationsMock
     *
     * @return ValidatorInterface|MockObject
     */
    public function createValidatorMock(MockObject $violationsMock, string $salutation, int $expectedValidateCallAmount): MockObject
    {
        $contextValidatorMock = $this->createMock(ContextualValidatorInterface::class);
        $contextValidatorMock->method('atPath')->willReturnSelf();
        $contextValidatorMock->method('getViolations')->willReturn($violationsMock);
        $contextValidatorMock->expects(static::exactly($expectedValidateCallAmount))->method('validate')->willReturnMap([
            [self::CUSTOMER_FIRSTNAME, $this->getNotBlankConstraints(), null, $contextValidatorMock],
            [self::CUSTOMER_LASTNAME, $this->getNotBlankConstraints(), null, $contextValidatorMock],
            [$salutation, $this->getNotBlankConstraints(), null, $contextValidatorMock],
            [self::CUSTOMER_EMAIL, $this->getNotBlankConstraints(), null, $contextValidatorMock],
        ]);

        $validatorMock = $this->createMock(ValidatorInterface::class);
        $validatorMock->expects(static::once())->method('startContext')->willReturn($contextValidatorMock);

        return $validatorMock;
    }

    /**
     * @return Shopware_Components_Config&MockObject
     */
    private function createConfigMock(bool $isSalutationRequired): MockObject
    {
        $configMock = $this->createMock(Shopware_Components_Config::class);
        $configMock->method('get')->willReturnMap([
            ['shopsalutationrequired', null, $isSalutationRequired],
            ['shopsalutations', null, 'mr, mrs, not_defined'],
        ]);

        return $configMock;
    }

    /**
     * @return ContextServiceInterface&MockObject
     */
    private function createContextMock(): MockObject
    {
        $shopContextMock = $this->createMock(ShopContextInterface::class);
        $shopContextMock->method('getShop')->willReturn(new Shop());

        $contextMock = $this->createMock(ContextServiceInterface::class);
        $contextMock->method('getShopContext')->willReturn($shopContextMock);

        return $contextMock;
    }

    private function createCustomer(string $salutation): Customer
    {
        $customer = new Customer();
        $customer->setFirstname(self::CUSTOMER_FIRSTNAME);
        $customer->setLastname(self::CUSTOMER_LASTNAME);
        $customer->setEmail(self::CUSTOMER_EMAIL);
        $customer->setSalutation($salutation);

        return $customer;
    }

    /**
     * @return array<int,NotBlank>
     */
    private function getNotBlankConstraints(): array
    {
        return [new NotBlank()];
    }
}
