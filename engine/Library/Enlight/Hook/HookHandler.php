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
 * Contains all data about the hook. (Hooked class, method, listener, position)
 *
 * The Enlight_Hook_HookHandler represents an single hook. The hook handler is registered
 * by the Enlight_Hook_Subscriber and is executed by the Enlight_Hook_Manager if the corresponding
 * original class method was executed.
 *
 * @deprecated Use the event subscriber
 */
class Enlight_Hook_HookHandler
{
    /**
     * Constant that defines that the class method should be overwritten.
     */
    const TypeReplace = 'replace';

    /**
     * Constant that defines that the hook method must be called before the original method.
     */
    const TypeBefore = 'before';

    /**
     * Constant that defines that the hook method must be called after the original method.
     */
    const TypeAfter = 'after';
}
