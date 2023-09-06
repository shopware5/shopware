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

namespace Shopware\Tests\Unit\Components\Template;

require __DIR__ . '/../../../../engine/Library/Enlight/Template/Plugins/modifier.tax.php';

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SmartyTaxModifierTest extends TestCase
{
    /**
     * @param string|int|float $tax
     *
     * @dataProvider taxProvider
     */
    public function testTaxModifierWithValid($tax, string $expected, string $locale): void
    {
        static::assertSame(
            $expected,
            smarty_modifier_tax($tax, $locale)
        );
    }

    /**
     * @param string|bool|null $tax
     *
     * @dataProvider taxProviderInvalid
     */
    public function testTaxModifierWithInvalid($tax, string $locale): void
    {
        $this->expectException(InvalidArgumentException::class);
        smarty_modifier_tax($tax, $locale);
    }

    public function taxProvider(): array
    {
        return [
            ['19.5', '19,50', 'de'],
            ['7', '7', 'de'],
            ['6.67', '6,67', 'de'],
            ['21.56', '21.56', 'en'],
            ['9', '9', 'en'],
            ['19.00', '19', 'de'],
            [19.00, '19', 'de'],
            [19, '19', 'en'],
            [19.5, '19,50', 'de'],
            [9999, '9.999', 'de'],
            [9999, '9,999', 'en'],
            [9999.99, '9.999,99', 'de'],
            [9999.99, '9,999.99', 'en'],
        ];
    }

    public function taxProviderInvalid(): array
    {
        return [
            ['true', 'en'],
            ['false', 'en'],
            ['null', 'en'],
            [true, 'en'],
            [false,  'en'],
            [null, 'en'],
            ['', 'en'],
        ];
    }
}
