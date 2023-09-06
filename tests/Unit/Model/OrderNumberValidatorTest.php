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

namespace Shopware\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\DBAL\Constraints\OrderNumber;
use Shopware\Components\Model\DBAL\Validator\OrderNumberValidator;
use Shopware\Components\OrderNumberValidator\RegexOrderNumberValidator;
use stdClass;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use TypeError;

class OrderNumberValidatorTest extends TestCase
{
    private OrderNumberValidator $validator;

    public function setUp(): void
    {
        $this->validator = new OrderNumberValidator(new RegexOrderNumberValidator('/^[a-zA-Z0-9-_.]+$/'));
    }

    public function testInvalidValues(): void
    {
        $values = [
            [0, 2, 3],
            ['somearray', 'somearray'],
            function () {},
            new stdClass(),
            new OrderNumber(),
        ];

        foreach ($values as $value) {
            $catch = $value;
            try {
                $this->validator->validate($value, new OrderNumber());
            } catch (UnexpectedTypeException $exception) {
                $catch = null;
            }

            static::assertNull($catch, sprintf('Type of variable "%s" did not throw an exception', \gettype($value)));
        }
    }

    public function testEmptyValue(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate(null, new OrderNumber());
        $this->validator->validate(0, new OrderNumber());
        $this->validator->validate('', new OrderNumber());
    }

    public function testWrongConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage("Expected argument of type \"Shopware\Components\Model\DBAL\Constraints\OrderNumber\", \"Symfony\Component\Validator\Constraints\IsTrue\" given");

        $this->validator->validate(null, new IsTrue());
    }

    public function testEmptyConstraint(): void
    {
        $this->expectException(TypeError::class);
        $this->validator->validate(null, null);
    }
}
