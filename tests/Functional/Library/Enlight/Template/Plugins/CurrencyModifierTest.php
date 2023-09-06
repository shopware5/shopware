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

namespace Shopware\Tests\Functional\Library\Enlight\Template\Plugins;

use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;

class CurrencyModifierTest extends TestCase
{
    /**
     * @dataProvider currencyValues
     *
     * @param float|string $currencyValue
     */
    public function testModifier($currencyValue, string $expectedFormattedCurrencyValue): void
    {
        $template = new Enlight_View_Default(new Enlight_Template_Manager());
        $template->Engine()->loadPlugin('smarty_modifier_currency');
        static::assertSame($expectedFormattedCurrencyValue, \smarty_modifier_currency($currencyValue));
    }

    public function currencyValues(): Generator
    {
        yield 'Currency value with comma' => ['1,23', '1,23&nbsp;&euro;'];
        yield 'Currency value as float' => [1.23, '1,23&nbsp;&euro;'];
        yield 'Currency value as numeric string' => ['1.23', '1,23&nbsp;&euro;'];
        yield 'Currency value with special chars' => ['\'"<>&1', '0,00&nbsp;&euro;'];
    }
}
