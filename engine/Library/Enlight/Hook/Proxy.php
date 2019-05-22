<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * The Enlight_Hook_Proxy is the interface for hooked classes.
 *
 * If a class is hooked, a proxy is generated for this class.
 * The generated class extends the origin class and implements the Enlight_Hook_Proxy interface.
 * Instead of the origin methods, the registered hook handler methods are executed.
 *
 * @category   Enlight
 * @package    Enlight_Hook
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
     * @param string $method
     * @param Enlight_Hook_HookExecutionContext $context
     */
    public function __pushHookExecutionContext($method, Enlight_Hook_HookExecutionContext $context);

    /**
     * @param string $method
     */
    public function __popHookExecutionContext($method);

    /**
     * @param string $method
     * @return Enlight_Hook_HookExecutionContext
     */
    public function __getCurrentHookProxyExecutionContext($method);

    /**
     * @param string $method
     * @return Enlight_Hook_HookManager
     */
    public function __getActiveHookManager($method);

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function executeParent($method, array $args = array());

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __executeOriginalMethod($method, array $args = array());
}
