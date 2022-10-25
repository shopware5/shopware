<?php

declare(strict_types=1);

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

namespace Shopware\Tests\Unit\Controllers\Backend;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\TestReflectionHelper;
use Shopware_Controllers_Backend_Config;

class ConfigTest extends TestCase
{
    /**
     * @param array<string, mixed> $elementData
     * @param mixed|null           $value
     *
     * @dataProvider getValidationData
     */
    public function testValidateConfigData(array $elementData, $value, bool $expectedResult): void
    {
        $validateDataMethod = TestReflectionHelper::getMethod(Shopware_Controllers_Backend_Config::class, 'validateData');

        $configController = new Shopware_Controllers_Backend_Config();

        static::assertSame($expectedResult, $validateDataMethod->invokeArgs($configController, [$elementData, $value]));
    }

    public function getValidationData(): Generator
    {
        yield 'Invalid min value' => [
            [
                'name' => 'foo',
                'type' => 'number',
                'options' => [
                    'minValue' => 2,
                    'maxValue' => 10,
                ],
            ],
            1,
            false,
        ];
        yield 'Invalid max value' => [
            [
                'name' => 'foo',
                'type' => 'number',
                'options' => [
                    'minValue' => 2,
                    'maxValue' => 10,
                ],
            ],
            11,
            false,
        ];
        yield 'Min value' => [
            [
                'name' => 'foo',
                'type' => 'number',
                'options' => [
                    'minValue' => 2,
                    'maxValue' => 10,
                ],
            ],
            2,
            true,
        ];
        yield 'Max value' => [
            [
                'name' => 'foo',
                'type' => 'number',
                'options' => [
                    'minValue' => 2,
                    'maxValue' => 10,
                ],
            ],
            10,
            true,
        ];
        yield 'Correct Value' => [
            [
                'name' => 'foo',
                'type' => 'number',
                'options' => [
                    'minValue' => 2,
                    'maxValue' => 10,
                ],
            ],
            5,
            true,
        ];
    }
}
