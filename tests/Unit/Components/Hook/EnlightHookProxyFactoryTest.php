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

namespace Shopware\Tests\Unit\Components\Hook;

use Enlight_Hook_HookManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EnlightHookProxyFactoryTest extends TestCase
{
    private TestProxyFactory $proxyFactory;

    public function setUp(): void
    {
        $hookManager = $this->createConfiguredMock(Enlight_Hook_HookManager::class, [
            'hasHooks' => true,
        ]);

        $this->proxyFactory = new TestProxyFactory($hookManager, 'ShopwareTests');
    }

    public function testGenerateBasicProxyClass(): void
    {
        $generatedClass = $this->invokeMethod($this->proxyFactory, [MyBasicTestClass::class]);
        $expectedClass = <<<'EOT'
<?php
class ShopwareTests_ShopwareTestsUnitComponentsHookMyBasicTestClassProxy extends \Shopware\Tests\Unit\Components\Hook\MyBasicTestClass implements Enlight_Hook_Proxy
{
    private $__hookProxyExecutionContexts = null;

    /**
     * @inheritdoc
     */
    public static function getHookMethods()
    {
        return ['myPublic', 'myVoid', 'myProtected'];
    }

    /**
     * @inheritdoc
     */
    public function __pushHookExecutionContext($method, \Enlight_Hook_HookExecutionContext $context)
    {
        $this->__hookProxyExecutionContexts[$method][] = $context;
    }

    /**
     * @inheritdoc
     */
    public function __popHookExecutionContext($method)
    {
        if (isset($this->__hookProxyExecutionContexts[$method])) {
            array_pop($this->__hookProxyExecutionContexts[$method]);
        }
    }

    /**
     * @inheritdoc
     */
    public function __getCurrentHookProxyExecutionContext($method)
    {
        if (!isset($this->__hookProxyExecutionContexts[$method]) || count($this->__hookProxyExecutionContexts[$method]) === 0) {
            return null;
        }

        $contextCount = count($this->__hookProxyExecutionContexts[$method]);
        $context = $this->__hookProxyExecutionContexts[$method][$contextCount - 1];

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function __getActiveHookManager($method)
    {
        $context = $this->__getCurrentHookProxyExecutionContext($method);
        $hookManager = ($context) ? $context->getHookManager() : Shopware()->Hooks();

        return $hookManager;
    }

    /**
     * @inheritdoc
     */
    public function executeParent($method, array $args = [])
    {
        $context = $this->__getCurrentHookProxyExecutionContext($method);
        if (!$context) {
            throw new Exception(
                sprintf('Cannot execute parent without hook execution context for method "%s"', $method)
            );
        }

        return $context->executeReplaceChain($args);
    }

    /**
     * @inheritdoc
     */
    public function __executeOriginalMethod($method, array $args = [])
    {
        return parent::{$method}(...$args);
    }

    /**
     * @inheritdoc
     */
    public function myPublic(string $bar, string $foo = 'bar', array $barBar = [], ?\Shopware\Tests\Unit\Components\Hook\MyInterface $fooFoo = null) : string
    {
        return $this->__getActiveHookManager(__FUNCTION__)->executeHooks(
            $this,
            __FUNCTION__,
            ['bar' => $bar, 'foo' => $foo, 'barBar' => $barBar, 'fooFoo' => $fooFoo]
        );
    }

    /**
     * @inheritdoc
     */
    public function myVoid() : void
    {
        $this->__getActiveHookManager(__FUNCTION__)->executeHooks(
            $this,
            __FUNCTION__,
            []
        );
    }

    /**
     * @inheritdoc
     */
    protected function myProtected($bar)
    {
        return $this->__getActiveHookManager(__FUNCTION__)->executeHooks(
            $this,
            __FUNCTION__,
            ['bar' => $bar]
        );
    }
}

EOT;
        static::assertSame($expectedClass, $generatedClass);
    }

    public function testGenerateProxyClassWithReferenceParameter(): void
    {
        $generatedClass = $this->invokeMethod($this->proxyFactory, [MyReferenceTestClass::class]);
        $expectedClass = <<<'EOT'
<?php
class ShopwareTests_ShopwareTestsUnitComponentsHookMyReferenceTestClassProxy extends \Shopware\Tests\Unit\Components\Hook\MyReferenceTestClass implements Enlight_Hook_Proxy
{
    private $__hookProxyExecutionContexts = null;

    /**
     * @inheritdoc
     */
    public static function getHookMethods()
    {
        return ['myPublic'];
    }

    /**
     * @inheritdoc
     */
    public function __pushHookExecutionContext($method, \Enlight_Hook_HookExecutionContext $context)
    {
        $this->__hookProxyExecutionContexts[$method][] = $context;
    }

    /**
     * @inheritdoc
     */
    public function __popHookExecutionContext($method)
    {
        if (isset($this->__hookProxyExecutionContexts[$method])) {
            array_pop($this->__hookProxyExecutionContexts[$method]);
        }
    }

    /**
     * @inheritdoc
     */
    public function __getCurrentHookProxyExecutionContext($method)
    {
        if (!isset($this->__hookProxyExecutionContexts[$method]) || count($this->__hookProxyExecutionContexts[$method]) === 0) {
            return null;
        }

        $contextCount = count($this->__hookProxyExecutionContexts[$method]);
        $context = $this->__hookProxyExecutionContexts[$method][$contextCount - 1];

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function __getActiveHookManager($method)
    {
        $context = $this->__getCurrentHookProxyExecutionContext($method);
        $hookManager = ($context) ? $context->getHookManager() : Shopware()->Hooks();

        return $hookManager;
    }

    /**
     * @inheritdoc
     */
    public function executeParent($method, array $args = [])
    {
        $context = $this->__getCurrentHookProxyExecutionContext($method);
        if (!$context) {
            throw new Exception(
                sprintf('Cannot execute parent without hook execution context for method "%s"', $method)
            );
        }

        return $context->executeReplaceChain($args);
    }

    /**
     * @inheritdoc
     */
    public function __executeOriginalMethod($method, array $args = [])
    {
        return parent::{$method}(...$args);
    }

    /**
     * @inheritdoc
     */
    public function myPublic(string &$bar, string $foo) : string
    {
        return $this->__getActiveHookManager(__FUNCTION__)->executeHooks(
            $this,
            __FUNCTION__,
            ['bar' => &$bar, 'foo' => $foo]
        );
    }
}

EOT;
        static::assertSame($expectedClass, $generatedClass);
    }

    /**
     * @param array<class-string> $parameters
     */
    private function invokeMethod(TestProxyFactory $object, array $parameters = []): string
    {
        $method = (new ReflectionClass($object))->getMethod('generateProxyClass');
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
