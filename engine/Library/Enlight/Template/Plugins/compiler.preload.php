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
 * Specifies a file to be preloaded using Link header
 */
class Smarty_Compiler_Preload extends Smarty_Internal_CompileBase
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
     * Overwrite optional attributes
     *
     * @var array
     */
    public $optional_attributes = ['_any'];

    /**
     * @param array                                  $args
     * @param Smarty_Internal_SmartyTemplateCompiler $compiler
     *
     * @return string
     */
    public function compile($args, $compiler)
    {
        // Check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        unset($_attr['nocache']);

        $options = $_attr;
        unset($options['file']);

        return '<?php '
             . 'echo Shopware()->Container()->get(\Shopware\Components\WebLinkManager::class)->preload(' . $_attr['file'] . ', ' . var_export($options, true) . ') ?>';
    }
}
