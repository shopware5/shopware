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

namespace Shopware\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\DBAL\Constraints;
use Shopware\Components\Model\DBAL\Validator\OrderNumberValidator;
use Shopware\Components\OrderNumberValidator\RegexOrderNumberValidator;

class OrderNumberValidatorTest extends TestCase
{
    /**
     * @var OrderNumberValidator
     */
    private $validator;

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
            new \stdClass(),
            new Constraints\OrderNumber(),
        ];

        $catch = null;
        foreach ($values as $value) {
            $catch = $value;
            try {
                $this->validator->validate($value, new Constraints\Ordernumber());
            } catch (\Symfony\Component\Validator\Exception\UnexpectedTypeException $exception) {
                $catch = null;
            }

            static::assertNull($catch, sprintf('Type of variable "%s" did not throw an exception', gettype($value)));
        }
    }

    public function testEmptyValue(): void
    {
        static::assertNull($this->validator->validate(null, new Constraints\OrderNumber()));
        static::assertNull($this->validator->validate(0, new Constraints\OrderNumber()));
        static::assertNull($this->validator->validate('', new Constraints\OrderNumber()));
    }

    public function testWrongConstraint(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage("Expected argument of type \"Shopware\Components\Model\DBAL\Constraints\OrderNumber\", \"Symfony\Component\Validator\Constraints\IsTrue\" given");

        $this->validator->validate(null, new \Symfony\Component\Validator\Constraints\IsTrue());
    }

    public function testEmptyConstraint(): void
    {
        $this->expectException(\TypeError::class);
        $this->validator->validate(null, null);
    }
}
