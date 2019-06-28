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

namespace Shopware\Tests\Unit\Library\Enlight\Controller;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DispatchFormatHelper;

class DefaultTest extends TestCase
{
    public function testFormatControllerNameReturnsProperControllerName()
    {
        $dispatcher = $this->getDispatcher();

        $testedStrings = [
            'abc' => 'Abc',
            'foo_' => 'Foo',
            'bar.' => 'Bar',
            'foobar123' => 'Foobar123',
            '-johndoe' => 'Johndoe',
            'nospace ' => 'Nospace',
            'UpperCase' => 'UpperCase',
            'emoji❤️' => 'Emoji',
            'germanVowelsÄÖÜ' => 'GermanVowels',
            'escaping%20' => 'Escaping20',
            'noneOfThese!"§$&/()=?`´<>#,;:*+/\\|…物' => 'NoneOfThese',
            '<nope>' => 'Nope',
            'null' => 'Null',
            '0' => '0',
        ];

        foreach ($testedStrings as $invalid => $expected) {
            static::assertSame($expected, $dispatcher->formatControllerName($invalid));
        }
    }

    public function testFormatActionNameReturnsProperActionName()
    {
        $dispatcher = $this->getDispatcher();

        $testedStrings = [
            'abc' => 'Abc',
            'foo_' => 'Foo',
            'bar.' => 'Bar',
            'foobar123' => 'Foobar123',
            '-johndoe' => 'Johndoe',
            'nospace ' => 'Nospace',
            'UpperCase' => 'UpperCase',
            'emoji❤️' => 'Emoji',
            'germanVowelsÄÖÜ' => 'GermanVowels',
            'escaping%20' => 'Escaping20',
            'noneOfThese!"§$&/()=?`´<>#,;:*+/\\|…物' => 'NoneOfThese',
            '<nope>' => 'Nope',
            'null' => 'Null',
            '0' => '0',
        ];

        foreach ($testedStrings as $invalid => $expected) {
            static::assertSame($expected, $dispatcher->formatActionName($invalid));
        }
    }

    public function testFormatModuleNameReturnsProperModuleName()
    {
        $dispatcher = $this->getDispatcher();

        $testedStrings = [
            'foo_' => 'Foo_',
            'foobar' => 'Foobar',
            'foo.bar' => 'FooBar',
            'foo-bar' => 'FooBar',
            'foo bar' => 'FooBar',
            ' foo bar' => 'FooBar',
        ];

        foreach ($testedStrings as $invalid => $expected) {
            static::assertSame($expected, $dispatcher->formatModuleName($invalid));
        }
    }

    /**
     * @return \Enlight_Controller_Dispatcher_Default
     */
    private function getDispatcher()
    {
        return new Enlight_Controller_Dispatcher_Default_TestMock();
    }
}

class Enlight_Controller_Dispatcher_Default_TestMock extends \Enlight_Controller_Dispatcher_Default
{
    public function __construct()
    {
        parent::__construct([], new \Shopware\Components\DependencyInjection\Container());
    }

    public function getDispatchFormatHelper()
    {
        return new DispatchFormatHelper();
    }
}
