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

namespace Shopware\Tests\Unit\Components\Hook;

use \Enlight_Hook_HookExecutionContext as HookExecutionContext;
use \Enlight_Hook_HookHandler as HookHandler;
use PHPUnit\Framework\TestCase;

/**
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class HookManagerTest extends TestCase
{
    const TEST_NAME_ARG = 'Test Name';
    const TEST_VALUES_ARG = [
        'foo' => 'bar'
    ];
    const TEST_ARGS = [
        'name' => self::TEST_NAME_ARG,
        'values' => self::TEST_VALUES_ARG
    ];
    const TEST_RETURN_VALUE = 'ReturnValue';
    const RECURSIVE_TEST_LIMIT_ARG = 2;
    const RECURSIVE_TEST_ARGS = [
        'limit' => self::RECURSIVE_TEST_LIMIT_ARG
    ];

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var \Enlight_Hook_HookManager
     */
    private $hookManager;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->eventManager = new \Enlight_Event_EventManager();
        $proxyDir = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . uniqid('hook-manager-test-' . rand(1000, 9000));
        if (is_dir($proxyDir)) {
            // Clear the directory
            array_map('unlink', glob($proxyDir . DIRECTORY_SEPARATOR . '*.*'));
        }
        $this->hookManager = new \Enlight_Hook_HookManager(
            $this->eventManager,
            new \Enlight_Loader(),
            [
                'proxyDir' => $proxyDir,
                'proxyNamespace' => 'Shopware_Test_Proxies'
            ]
        );
        if (!class_exists($this->hookManager->getProxyFactory()->getProxyClassName(HookManagerTestTarget::class))) {
            // Create a proxy after adding hooks on both its methods. It is necessary to do this here, because the name
            // of the proxy class won't change between the tests and hence the generated class will loaded as soon as
            // the first proxy is instanciated.
            $this->addHookListener(HookManagerTestTarget::TEST_METHOD_NAME, HookHandler::TypeReplace);
            $this->addHookListener(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, HookHandler::TypeReplace);
            $this->addHookListener(HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME, HookHandler::TypeReplace);
            $this->addHookListener(HookManagerTestTarget::VARIABLE_NAME_COLLISION_TEST_METHOD_NAME, HookHandler::TypeAfter);
            $proxyClass = $this->hookManager->getProxy(HookManagerTestTarget::class);
            $proxy = new $proxyClass();
        }
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(\Enlight_Hook_HookManager::class, $this->hookManager);
    }

    public function testHasHooks()
    {
        // Assert that no hooks exist prior to this test
        $hasHooks = $this->hookManager->hasHooks(HookManagerTestTarget::class, HookManagerTestTarget::TEST_METHOD_NAME);
        $this->assertFalse($hasHooks);

        // Test 'before' hook
        $this->addHookListener(HookManagerTestTarget::TEST_METHOD_NAME, HookHandler::TypeBefore);
        $hasHooks = $this->hookManager->hasHooks(HookManagerTestTarget::class, HookManagerTestTarget::TEST_METHOD_NAME);
        $this->assertTrue($hasHooks);
        $this->eventManager->reset();

        // Test 'replace' hook
        $this->addHookListener(HookManagerTestTarget::TEST_METHOD_NAME, HookHandler::TypeReplace);
        $hasHooks = $this->hookManager->hasHooks(HookManagerTestTarget::class, HookManagerTestTarget::TEST_METHOD_NAME);
        $this->assertTrue($hasHooks);
        $this->eventManager->reset();

        // Test 'after' hook
        $this->addHookListener(HookManagerTestTarget::TEST_METHOD_NAME, HookHandler::TypeAfter);
        $hasHooks = $this->hookManager->hasHooks(HookManagerTestTarget::class, HookManagerTestTarget::TEST_METHOD_NAME);
        $this->assertTrue($hasHooks);
        $this->eventManager->reset();
    }

    /**
     * Tests the execution of a 'before' hook.
     */
    public function testExecuteHooksBefore()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeBefore,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Modify the 'name' arg to change the return value of the parent implementation
                $args->name .= '_mod';
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals((self::TEST_NAME_ARG . '_mod'), $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(1, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of a 'before' hook on a protected method.
     */
    public function testExecuteHooksBeforeProtectedMethod()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            HookHandler::TypeBefore,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalProtectedMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Modify the 'name' arg to change the return value of the parent implementation
                $args->name .= '_mod';
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals((self::TEST_NAME_ARG . '_mod'), $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(1, $proxy->originalProtectedMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook.
     */
    public function testExecuteHooksReplace()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Overwrite the return value
                $args->setReturn(self::TEST_RETURN_VALUE);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals(self::TEST_RETURN_VALUE, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(0, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook, which calls the parent implementation and uses its return value.
     */
    public function testExecuteHooksReplaceWithParentExection()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Modify the 'name' arg to change the return value of the parent implementation
                $args->name .= '_mod';

                // Call the parent
                $args->getSubject()->executeParent(HookManagerTestTarget::TEST_METHOD_NAME, $args->getArgs());
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals((self::TEST_NAME_ARG . '_mod'), $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(1, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook, which calls the parent implementation repeatedly.
     */
    public function testExecuteHooksReplaceWithRepeatedParentExection()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Call parent three times
                $args->getSubject()->executeParent(HookManagerTestTarget::TEST_METHOD_NAME, $args->getArgs());
                $args->getSubject()->executeParent(HookManagerTestTarget::TEST_METHOD_NAME, $args->getArgs());
                $args->getSubject()->executeParent(HookManagerTestTarget::TEST_METHOD_NAME, $args->getArgs());

                // Change the return value
                $args->setReturn(self::TEST_RETURN_VALUE);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals(self::TEST_RETURN_VALUE, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(3, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of three 'replace' hooks on the same method. In particular the correct order of the hook
     * calls as well as the respective return values are asserted.
     */
    public function testExecuteHooksReplaceMultiple()
    {
        $firstHookCallCounter = 0;
        $secondHookCallCounter = 0;
        $thirdHookCallCounter = 0;
        $firstHookReturnValue = self::TEST_RETURN_VALUE . '_first';
        $secondHookReturnValue = self::TEST_RETURN_VALUE . '_second';
        $thirdHookReturnValue = self::TEST_RETURN_VALUE . '_third';

        // Register first hook (to be executed first)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter,
                $firstHookReturnValue,
                $secondHookReturnValue
            ) {
                $firstHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());
                // Second and third hooks should not have been called before this hook is called
                $this->assertEquals(0, $secondHookCallCounter);
                $this->assertEquals(0, $thirdHookCallCounter);

                // Call parent
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );

                // Second and third hooks should have been called now
                $this->assertEquals(1, $secondHookCallCounter);
                $this->assertEquals(1, $thirdHookCallCounter);
                $this->assertEquals($secondHookReturnValue, $parentReturnValue);

                // Overwrite return value
                $args->setReturn($firstHookReturnValue);
            }
        );

        // Register second hook (to be executed second)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter,
                $firstHookReturnValue,
                $secondHookReturnValue,
                $thirdHookReturnValue
            ) {
                $secondHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());
                // First hook should have already been called when this hook is called
                $this->assertEquals(1, $firstHookCallCounter);
                // Third hook should not have been called before this hook is called
                $this->assertEquals(0, $thirdHookCallCounter);

                // Call parent
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );

                // Third hook should have been called now
                $this->assertEquals(1, $thirdHookCallCounter);
                $this->assertEquals($thirdHookReturnValue, $parentReturnValue);

                // Overwrite return value
                $args->setReturn($secondHookReturnValue);
            }
        );

        // Register third hook (to be executed third, just before the original/parent method)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter,
                $firstHookReturnValue,
                $secondHookReturnValue,
                $thirdHookReturnValue
            ) {
                $thirdHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());
                // First and second hooks should have already been called when this hook is called
                $this->assertEquals(1, $firstHookCallCounter);
                $this->assertEquals(1, $secondHookCallCounter);

                // Call parent
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );
                $this->assertEquals(self::TEST_NAME_ARG, $parentReturnValue);

                // Overwrite return value
                $args->setReturn($thirdHookReturnValue);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals($firstHookReturnValue, $returnValue);
        // All hooks as well as the original method should have only been called once
        $this->assertEquals(1, $firstHookCallCounter);
        $this->assertEquals(1, $secondHookCallCounter);
        $this->assertEquals(1, $thirdHookCallCounter);
        $this->assertEquals(1, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of tow 'replace' hooks on the same method, of which the first one executes its parent twice.
     * This should result in the second hook and the original method to be called twice each.
     */
    public function testExecuteHooksReplaceMultipleWithRepeatedParentExection()
    {
        $firstHookCallCounter = 0;
        $secondHookCallCounter = 0;
        $firstHookReturnValue = self::TEST_RETURN_VALUE . '_first';
        $secondHookReturnValue = self::TEST_RETURN_VALUE . '_second';

        // Register first hook (to be executed first)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                $firstHookReturnValue,
                $secondHookReturnValue
            ) {
                $firstHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());
                // Second hook should not have been called before this hook is called
                $this->assertEquals(0, $secondHookCallCounter);

                // Call parent once
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );

                // Second hook should have been called once now
                $this->assertEquals(1, $secondHookCallCounter);
                $this->assertEquals($secondHookReturnValue, $parentReturnValue);

                // Call parent again
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );

                // Second hook should have been called twice now
                $this->assertEquals(2, $secondHookCallCounter);
                $this->assertEquals($secondHookReturnValue, $parentReturnValue);

                // Overwrite return value
                $args->setReturn($firstHookReturnValue);
            }
        );

        // Register second hook (to be executed second)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                $firstHookReturnValue,
                $secondHookReturnValue
            ) {
                $secondHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertTrue(in_array($args->getSubject()->originalMethodCallCounter, [0, 1]));
                if ($secondHookCallCounter === 1) {
                    $this->assertNull($args->getReturn());
                } else {
                    // Expect the own return value to be set already
                    $this->assertEquals($secondHookReturnValue, $args->getReturn());
                }
                // First hook should have already been called exactly once when this hook is called
                $this->assertEquals(1, $firstHookCallCounter);

                // Call parent
                $parentReturnValue = $args->getSubject()->executeParent(
                    HookManagerTestTarget::TEST_METHOD_NAME,
                    $args->getArgs()
                );
                $this->assertEquals(self::TEST_NAME_ARG, $parentReturnValue);

                // Overwrite return value
                $args->setReturn($secondHookReturnValue);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals($firstHookReturnValue, $returnValue);
        // The first hook should have been called only once, but the second hook and the original method should have
        // been called twice, since the first hook calls 'executeParent()' twice!
        $this->assertEquals(1, $firstHookCallCounter);
        $this->assertEquals(2, $secondHookCallCounter);
        $this->assertEquals(2, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of tow 'replace' hooks on the same method.
     */
    public function testExecuteHooksReplaceMultipleWithoutParentExection()
    {
        $firstHookCallCounter = 0;
        $firstHookReturnValue = self::TEST_RETURN_VALUE . '_first';
        $secondHookReturnValue = self::TEST_RETURN_VALUE . '_second';

        // Register first hook (to be executed first)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$firstHookCallCounter, $firstHookReturnValue) {
                $firstHookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Overwrite return value
                $args->setReturn($firstHookReturnValue);
            }
        );

        // Register second hook (should never be executed)
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) {
                // This hook should not be executed!
                $this->assertTrue(false);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals($firstHookReturnValue, $returnValue);
        // Only first hook should have been called
        $this->assertEquals(1, $firstHookCallCounter);
        $this->assertEquals(0, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook on a recursive method.
     */
    public function testExecuteHooksReplaceRecursive()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertEquals(self::RECURSIVE_TEST_LIMIT_ARG, $args->limit);
                $this->assertEquals(0, $args->getSubject()->originalRecursiveMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Overwrite return value
                $args->setReturn(0);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            self::RECURSIVE_TEST_ARGS
        );
        $this->assertEquals(0, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(0, $proxy->originalRecursiveMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook, which calls the parent implementation and uses its return value, on a
     * recursive method
     */
    public function testExecuteHooksReplaceWithParentExectionRecursive()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                // The limit should only be reduced after this hook is called
                $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG - $hookCallCounter + 1), $args->limit);
                // The original method should have been called less often than this hook
                $this->assertEquals(($hookCallCounter - 1), $args->getSubject()->originalRecursiveMethodCallCounter);
                // The return value should not be set in any hook call, because it is recursively resovled
                $this->assertNull($args->getReturn());

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            self::RECURSIVE_TEST_ARGS
        );
        $this->assertEquals(self::RECURSIVE_TEST_LIMIT_ARG, $returnValue);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $hookCallCounter);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $proxy->originalRecursiveMethodCallCounter);
    }

    /**
     * Tests the execution of three 'replace' hooks on the same recursive method. In particular the correct order of the
     * hook calls as well as the respective return values are asserted.
     */
    public function testExecuteHooksReplaceMultipleRecursive()
    {
        $firstHookCallCounter = 0;
        $secondHookCallCounter = 0;
        $thirdHookCallCounter = 0;

        // Register first hook (to be executed first)
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter
            ) {
                $firstHookCallCounter++;
                // The limit should only be reduced after this hook is called
                $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG - $firstHookCallCounter + 1), $args->limit);
                // The original method and the other hooks should have been called less often than this hook
                $this->assertEquals(($firstHookCallCounter - 1), $args->getSubject()->originalRecursiveMethodCallCounter);
                $this->assertEquals(($firstHookCallCounter - 1), $secondHookCallCounter);
                $this->assertEquals(($firstHookCallCounter - 1), $thirdHookCallCounter);
                // The return value should not be set in any hook call, because it is recursively resovled
                $this->assertNull($args->getReturn());

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);

                // Second and third hooks should have been called as many times as this hook
                $this->assertEquals($firstHookCallCounter, $secondHookCallCounter);
                $this->assertEquals($firstHookCallCounter, $thirdHookCallCounter);
            }
        );

        // Register second hook (to be executed second)
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter
            ) {
                $secondHookCallCounter++;
                // The limit should only be reduced after this hook is called
                $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG - $secondHookCallCounter + 1), $args->limit);
                // The original method and the third hook should have been called less often than this hook
                $this->assertEquals(($secondHookCallCounter - 1), $args->getSubject()->originalRecursiveMethodCallCounter);
                $this->assertEquals(($secondHookCallCounter - 1), $thirdHookCallCounter);
                // The first hook should have been called as many times as this hook
                $this->assertEquals($secondHookCallCounter, $firstHookCallCounter);
                // The return value should not be set in any hook call, because it is recursively resovled
                $this->assertNull($args->getReturn());

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);

                // Third hook should have been called as many times as this hook
                $this->assertEquals($secondHookCallCounter, $thirdHookCallCounter);
            }
        );

        // Register third hook (to be executed third, just before the original/parent method)
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter,
                &$thirdHookCallCounter
            ) {
                $thirdHookCallCounter++;
                // The limit should only be reduced after this hook is called
                $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG - $thirdHookCallCounter + 1), $args->limit);
                // The original method should have been called less often than this hook
                $this->assertEquals(($thirdHookCallCounter - 1), $args->getSubject()->originalRecursiveMethodCallCounter);
                // The other hooks should have been called as many times as this hook
                $this->assertEquals($thirdHookCallCounter, $firstHookCallCounter);
                $this->assertEquals($thirdHookCallCounter, $secondHookCallCounter);
                // The return value should not be set in any hook call, because it is recursively resovled
                $this->assertNull($args->getReturn());

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            self::RECURSIVE_TEST_ARGS
        );
        $this->assertEquals(self::RECURSIVE_TEST_LIMIT_ARG, $returnValue);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $firstHookCallCounter);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $secondHookCallCounter);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $thirdHookCallCounter);
        $this->assertEquals((self::RECURSIVE_TEST_LIMIT_ARG + 1), $proxy->originalRecursiveMethodCallCounter);
    }

    /**
     * Tests the execution of tow 'replace' hooks on the same recursive method, of which the first one executes its
     * parent twice. This should result in the second hook and the original method to be called twice as often as the
     * first hook.
     */
    public function testExecuteHooksReplaceMultipleWithRepeatedParentExectionRecursive()
    {
        $firstHookCallCounter = 0;
        $secondHookCallCounter = 0;

        // Register first hook (to be executed first)
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter
            ) {
                $firstHookCallCounter++;

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);

                // Call the parent again expect its result to be still equal to the current limit, because repeated
                // calls to a recursive 'executeParent()' all spawn their own context
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);
            }
        );

        // Register second hook (to be executed second)
        $this->addHookListener(
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (
                &$firstHookCallCounter,
                &$secondHookCallCounter
            ) {
                $secondHookCallCounter++;

                // Call the parent and expect its result to be equal to the current limit
                $expectedReturnValue = $args->limit;
                $returnValue = $args->getSubject()->executeParent(HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME, $args->getArgs());
                $this->assertEquals($expectedReturnValue, $returnValue);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::RECURSIVE_TEST_METHOD_NAME,
            self::RECURSIVE_TEST_ARGS
        );
        // Although the first hook calls 'executeParent()' twice, the result will still be correct, because repeated
        // calls to a recursive parent all spawn their own context
        $this->assertEquals(self::RECURSIVE_TEST_LIMIT_ARG, $returnValue);
        // With a limit of 2, the first hook should have been called 7 times:
        //      1 initial call
        //    + 3 recursive calls triggered when calling 'executeParent()' for the first time in the initial hook call
        //    + 3 recursive calls triggered when calling 'executeParent()' a second time in the initial hook call
        $this->assertEquals((1 + 2 * (self::RECURSIVE_TEST_LIMIT_ARG + 1)), $firstHookCallCounter);
        // Since every call of the first hook calls 'executeParent()' twice, all methods following in the execution
        // chain should be called twice as often as the first hook. Hence, with a limit of 2, the second hook and the
        // original method should have been called 14 times each.
        $this->assertEquals((2 * $firstHookCallCounter), $secondHookCallCounter);
        $this->assertEquals((2 * $firstHookCallCounter), $proxy->originalRecursiveMethodCallCounter);
    }

    /**
     * Tests the execution of a 'replace' hook on a protected method.
     */
    public function testExecuteHooksReplaceProtectedMethod()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            HookHandler::TypeReplace,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(0, $args->getSubject()->originalProtectedMethodCallCounter);
                $this->assertNull($args->getReturn());

                // Overwrite the return value
                $args->setReturn(self::TEST_RETURN_VALUE);
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals(self::TEST_RETURN_VALUE, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(0, $proxy->originalProtectedMethodCallCounter);
    }

    /**
     * Tests the execution of an 'after' hook.
     */
    public function testExecuteHooksAfter()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::TEST_METHOD_NAME,
            HookHandler::TypeAfter,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(1, $args->getSubject()->originalMethodCallCounter);
                $this->assertEquals(self::TEST_NAME_ARG, $args->getReturn());

                // Overwrite the return value
                return self::TEST_RETURN_VALUE;
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals(self::TEST_RETURN_VALUE, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(1, $proxy->originalMethodCallCounter);
    }

    /**
     * Tests the execution of an 'after' hook on a protected method.
     */
    public function testExecuteHooksAfterProtectedMethod()
    {
        $hookCallCounter = 0;
        $this->addHookListener(
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            HookHandler::TypeAfter,
            function (\Enlight_Hook_HookArgs $args) use (&$hookCallCounter) {
                $hookCallCounter++;
                $this->assertHookArgs($args);
                $this->assertEquals(1, $args->getSubject()->originalProtectedMethodCallCounter);
                $this->assertEquals(self::TEST_NAME_ARG, $args->getReturn());

                // Overwrite the return value
                return self::TEST_RETURN_VALUE;
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::PROTECTED_TEST_METHOD_NAME,
            self::TEST_ARGS
        );
        $this->assertEquals(self::TEST_RETURN_VALUE, $returnValue);
        $this->assertEquals(1, $hookCallCounter);
        $this->assertEquals(1, $proxy->originalProtectedMethodCallCounter);
    }

    /**
     * Tests that method parameters named '$class', '$method', '$context' or '$hookManager' are not overwritten by any
     * of the helper variables used in the generated proxy method or the hook args.
     */
    public function testVariableNamesDontCollide()
    {
        $classArg = 'A';
        $methodArg = 'B';
        $contextArg = 'C';
        $hookManagerArg = 'D';
        $expectedReturnValue = $classArg . $methodArg . $contextArg . $hookManagerArg;

        $this->addHookListener(
            HookManagerTestTarget::VARIABLE_NAME_COLLISION_TEST_METHOD_NAME,
            HookHandler::TypeAfter,
            function (\Enlight_Hook_HookArgs $args) use ($classArg, $methodArg, $contextArg, $hookManagerArg, $expectedReturnValue) {
                // Assert that no parameters were overwritten by the proxy
                $this->assertCount(4, $args->getArgs());
                $this->assertEquals($classArg, $args->get('class'));
                $this->assertEquals($methodArg, $args->get('method'));
                $this->assertEquals($contextArg, $args->get('context'));
                $this->assertEquals($hookManagerArg, $args->get('hookManager'));
                $this->assertEquals($expectedReturnValue, $args->getReturn());

                // Assert that the subject and method name of the hook args is correct
                $this->assertInstanceOf(HookManagerTestTarget::class, $args->getSubject());
                $this->assertEquals(HookManagerTestTarget::VARIABLE_NAME_COLLISION_TEST_METHOD_NAME, $args->getMethod());
            }
        );

        $proxy = $this->createProxy();
        $returnValue = $this->hookManager->executeHooks(
            $proxy,
            HookManagerTestTarget::VARIABLE_NAME_COLLISION_TEST_METHOD_NAME,
            [
                'class' => $classArg,
                'method' => $methodArg,
                'context' => $contextArg,
                'hookManager' => $hookManagerArg,
            ]
        );
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * @param string $methodName
     * @param string $hookType
     * @param callable|null $callback
     */
    private function addHookListener($methodName, $hookType, callable $callback = null)
    {
        $callback = ($callback) ?: function (\Enlight_Hook_HookArgs $args) {
            // pass
        };
        $this->eventManager->addListener(
            HookExecutionContext::createHookEventName(HookManagerTestTarget::class, $methodName, $hookType),
            $callback
        );
    }

    /**
     * @return Enlight_Hook_Proxy
     */
    private function createProxy()
    {
        $proxyClass = $this->hookManager->getProxy(HookManagerTestTarget::class);
        $proxy = new $proxyClass();

        return $proxy;
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    private function assertHookArgs(\Enlight_Hook_HookArgs $args)
    {
        $this->assertEquals(self::TEST_NAME_ARG, $args->name);
        $this->assertArraySubset(self::TEST_VALUES_ARG, $args->values);
    }
}
