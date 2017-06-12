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

namespace Shopware\Tests\Unit\Enlight\Hook;

use Shopware\Tests\Unit\Enlight\Hook\ProxyFactoryTestSamples\HookManagerMock;
use Shopware\Tests\Unit\Enlight\Hook\ProxyFactoryTestSamples\TestClass;
use PHPUnit\Framework\TestCase;

/**
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProxyFactoryTest extends TestCase
{
    public function testGenerateMethods()
    {
        // Create a proxy factory instance for testing
        $proxyFactory = new \Enlight_Hook_ProxyFactory(
            new HookManagerMock(),
            '',
            __DIR__
        );

        // Call its protected 'generateMethods' method for the TestClass
        $reflectionProxyFactory = new \ReflectionClass($proxyFactory);
        $method = $reflectionProxyFactory->getMethod('generateMethods');
        $method->setAccessible(true);
        list($generatedMethods, $generatedCode) = array_values($method->invokeArgs($proxyFactory, [TestClass::class]));

        // Check the results
        $this->assertCount(4, $generatedMethods);
        $this->assertNotEquals(false, strpos($generatedCode, 'public function methodWithOptionalParameter($a = NULL)'));
        $this->assertNotEquals(false, strpos($generatedCode, 'public function methodWithOptionalArrayParameter(array $a = NULL)'));
        $this->assertNotEquals(false, strpos($generatedCode, 'public function methodWithOptionalCallableParameter(callable $a = NULL)'));
        $this->assertNotEquals(false, strpos($generatedCode, 'public function methodWithOptionalObjectParameter(Shopware\Tests\Unit\Enlight\Hook\ProxyFactoryTestSamples\TestClass $a = NULL)'));
    }
}
