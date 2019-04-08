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

namespace Shopware\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DispatchFormatHelper;

class DispatchFormatHelperTest extends TestCase
{
    public function testFormatNameForRequestOnlyAllowsAlphanumericalCharsAndUnderscore()
    {
        $testResults = [
            'abc' => 'abc',
            'foo_' => 'foo_',
            'bar.' => 'bar',
            'foobar123' => 'foobar123',
            '-johndoe' => 'johndoe',
            'nospace ' => 'nospace',
            'UpperCase' => 'UpperCase',
            'emoji❤️' => 'emoji',
            'germanVowelsÄÖÜ' => 'germanVowels',
            'escaping%20' => 'escaping20',
            'noneOfThese!"§$&/()=?`´<>#,;:*+/\\|…物' => 'noneOfThese',
            '<nope>' => 'nope',
            'null' => 'null',
            '0' => '0',
        ];

        $nameFormatter = $this->getDispatchFormatHelper();

        foreach ($testResults as $testValue => $expectedResult) {
            static::assertSame($expectedResult, $nameFormatter->formatNameForRequest($testValue));
        }
    }

    public function testFormatNameForRequestAllowsDotsOnlyForControllers()
    {
        $nameFormatter = $this->getDispatchFormatHelper();

        $testString = 'foo.bar';
        $testStringForController = $nameFormatter->formatNameForRequest($testString, true);
        $testStringForModule = $nameFormatter->formatNameForRequest($testString);
        static::assertSame('foo.bar', $testStringForController);
        static::assertSame('foobar', $testStringForModule);
    }

    public function testFormatNameForRequestCompatibleWithEmptyStrings()
    {
        $nameFormatter = $this->getDispatchFormatHelper();

        $testString = '';
        $testStringForController = $nameFormatter->formatNameForRequest($testString, true);
        $testStringForModule = $nameFormatter->formatNameForRequest($testString);
        static::assertSame('', $testStringForController);
        static::assertSame('', $testStringForModule);
    }

    public function testFormatNameForDispatchReplacesSpecialCharactersIntoUpperCamelcase()
    {
        $testResults = [
            'foobar' => 'Foobar',
            'foo.bar' => 'FooBar',
            'foo-bar' => 'FooBar',
            'foo bar' => 'FooBar',
            ' foo bar' => 'FooBar',
        ];

        $nameFormatter = $this->getDispatchFormatHelper();

        foreach ($testResults as $testValue => $expectedResult) {
            static::assertSame($expectedResult, $nameFormatter->formatNameForDispatch($testValue));
        }
    }

    public function testFormatNameForDispatchKeepsUnderscore()
    {
        $testResult = $this->getDispatchFormatHelper()->formatNameForDispatch('foo_bar');
        static::assertSame('Foo_Bar', $testResult);
    }

    private function getDispatchFormatHelper()
    {
        return new DispatchFormatHelper();
    }
}
