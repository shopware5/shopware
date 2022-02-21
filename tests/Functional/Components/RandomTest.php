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

namespace Shopware\Tests\Functional\Components;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Random;

class RandomTest extends Enlight_Components_Test_TestCase
{
    public function testGeneratePassword(): void
    {
        $sets = [];
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $sets[] = '23456789';
        $sets[] = '!@#$%&*?';

        $chars = implode('', $sets);

        $password = Random::generatePassword();
        static::assertEquals(15, \strlen($password));

        for ($i = 0, $iMax = \strlen($password); $i < $iMax; ++$i) {
            $char = $password[$i];

            static::assertStringContainsString($char, $chars);

            foreach ($sets as $key => $set) {
                if (str_contains($set, $char)) {
                    unset($sets[$key]);
                }
            }
        }

        static::assertEmpty($sets);
    }

    public function testGetBoolean(): void
    {
        $result = Random::getBoolean();
        static::assertIsBool($result);
    }

    public function testGetInteger(): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $result = Random::getInteger(-100000, 100000);
            static::assertLessThanOrEqual(100000, $result);
            static::assertGreaterThanOrEqual(-100000, $result);
        }
    }

    public function testGetFloat(): void
    {
        $results = [];
        for ($i = 0; $i < 1000; ++$i) {
            $result = Random::getFloat();
            static::assertIsFloat($result);
            static::assertLessThanOrEqual(1, $result);
            static::assertGreaterThanOrEqual(0, $result);
            static::assertNotContains($result, $results);

            $results[] = $result;
        }
    }
}
