<?php
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

use Enlight_Hook;

class HookManagerTestTarget implements Enlight_Hook
{
    public const TEST_METHOD_NAME = 'testMethod';
    public const RECURSIVE_TEST_METHOD_NAME = 'recursiveTestMethod';
    public const PROTECTED_TEST_METHOD_NAME = 'protectedTestMethod';
    public const VARIABLE_NAME_COLLISION_TEST_METHOD_NAME = 'variableNameCollisionTestMethod';
    public const VOID_TEST_METHOD_NAME = 'voidTestMethod';

    public $originalMethodCallCounter = 0;

    public $originalRecursiveMethodCallCounter = 0;

    public $originalProtectedMethodCallCounter = 0;

    public function testMethod($name, array $values = [])
    {
        ++$this->originalMethodCallCounter;

        return $name;
    }

    public function recursiveTestMethod($limit)
    {
        ++$this->originalRecursiveMethodCallCounter;

        if ($limit === 0) {
            return 0;
        }

        return 1 + $this->recursiveTestMethod($limit - 1);
    }

    public function variableNameCollisionTestMethod($class, $method, $context, $hookManager)
    {
        return $class . $method . $context . $hookManager;
    }

    public function voidTestMethod(): void
    {
        ++$this->originalMethodCallCounter;
    }

    protected function protectedTestMethod($name, array $values = [])
    {
        ++$this->originalProtectedMethodCallCounter;

        return $name;
    }
}
