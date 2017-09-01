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

/**
 * The Enlight_Hook_Proxy is the interface for hooked classes.
 *
 * If a class is hooked, a proxy is generated for this class.
 * The generated class extends the origin class and implements the Enlight_Hook_Proxy interface.
 * Instead of the origin methods, the registered hook handler methods are executed.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
interface Enlight_Hook_Proxy
{
    /**
     * @return string[]
     */
    public static function getHookMethods();

    /**
     * @param string                            $method
     * @param Enlight_Hook_HookExecutionContext $context
     */
    public function pushHookExecutionContext($method, Enlight_Hook_HookExecutionContext $context);

    /**
     * @param string $method
     */
    public function popHookExecutionContext($method);

    /**
     * @param string $method
     *
     * @return Enlight_Hook_HookExecutionContext
     */
    public function getCurrentHookProxyExecutionContext($method);

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function executeParent($method, array $args = []);
}
