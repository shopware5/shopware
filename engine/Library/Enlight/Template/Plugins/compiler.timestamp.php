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
 * @package    Enlight_Template_Plugins
 * @copyright  Copyright © shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Returns the current time measured in the number of seconds
 * since the Unix Epoch (January 1 1970 00:00:00 GMT).
 */
class Smarty_Compiler_Timestamp extends Smarty_Internal_CompileBase
{
    /**
     * @param $args
     * @param $compiler
     * @return int
     */
    public function compile($args, $compiler)
    {
        return time();
    }
}