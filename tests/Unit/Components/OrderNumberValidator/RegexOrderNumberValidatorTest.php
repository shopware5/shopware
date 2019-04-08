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

namespace Shopware\Tests\Unit\Components\OrderNumberValidator;

use PHPUnit\Framework\TestCase;
use Shopware\Components\OrderNumberValidator\Exception\InvalidOrderNumberException;
use Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface;
use Shopware\Components\OrderNumberValidator\RegexOrderNumberValidator;

class RegexOrderNumberValidatorTest extends TestCase
{
    /**
     * @var OrderNumberValidatorInterface
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new RegexOrderNumberValidator('/^[a-zA-Z0-9-_.]+$/');
    }

    public function testValidOrderNumbers(): void
    {
        $ordernumbers = [
            '5',
            '0',
            '4711',
            (string) PHP_INT_MIN,
            (string) PHP_INT_MAX,
            'SWAGNUMBER',
            'SWAG-NUMBER',
            'SWAG_NUMBER',
            'SWAG4711',
            'SWAG-4711',
            'SWAG_4711',
            '.',
            '.815',
            'SW.815',
        ];

        $catch = null;
        foreach ($ordernumbers as $ordernumber) {
            $catch = null;

            try {
                $this->validator->validate($ordernumber);
            } catch (InvalidOrdernumberException $exception) {
                $catch = $ordernumber;
            }

            static::assertNull($catch, sprintf('Ordernumber "%s" is not valid', $ordernumber));
        }
    }

    public function testInvalidOrderNumbers(): void
    {
        $ordernumbers = [
            '',
            'SWAG NUMBER',
            'SWAG@NUMBER',
            '💙',
            '47,12€',
            '47.12€',
            'SWAG@123',
            'SWAG 123',
            '456 123',
            '456,123',
        ];

        foreach ($ordernumbers as $ordernumber) {
            try {
                $this->validator->validate($ordernumber);
            } catch (InvalidOrdernumberException $exception) {
                $ordernumber = null;
            }

            static::assertNull($ordernumber, sprintf('Ordernumber "%s" should be invalid', $ordernumber));
        }
    }
}
