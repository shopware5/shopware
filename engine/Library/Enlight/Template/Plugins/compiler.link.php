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
 * Builds an Enlight Link based on given controller and action.
 * The params array knows the following key:
 * - file     : this key must be filled with a filename
 * - fullPath : if this key is filled, a whole link will be returned http[s]://....
 */
class Smarty_Compiler_Link extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['file'];

    /**
     * Array of names of valid option flags
     *
     * @var array
     */
    public $option_flags = ['fullPath'];

    /**
     * @param array  $args
     * @param object $compiler
     *
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if (empty($_attr['file'])) {
            return false;
        }
        $file = trim($_attr['file'], '"\'');
        $fullPath = !empty($_attr['fullPath']);

        if (preg_match('/^([\'"]?)[a-zA-Z0-9\/\.\-\_]+(\\1)$/', $_attr['file'], $match)) {
            $compiler->smarty->loadPlugin('smarty_function_flink');

            return smarty_function_flink([
                'file' => $file,
                'fullPath' => $fullPath,
            ], $compiler);
        }

        return '<?php $_smarty_tpl->smarty->loadPlugin("smarty_function_flink"); echo smarty_function_flink(array(' .
            '"file" => ' . $_attr['file'] . ', ' .
            '"fullPath" => ' . var_export($fullPath, true) .
            '), $_smarty_tpl); ?>';
    }
}
