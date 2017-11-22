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
 * Specifies a file to be prefetch using Link header
 */
class Smarty_Compiler_Prefetch extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['uri'];

    /**
     * Overwrite optional attributes
     * @var array
     */
    public $optional_attributes = ['_any'];

    /**
     * @param array $args
     * @param Smarty_Internal_SmartyTemplateCompiler $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        unset($_attr['nocache']);

        $options = $_attr;
        unset($options['uri']);

        return '<?php '
             . 'echo Shopware()->Container()->get(\'web_link_manager\')->prefetch(' . $_attr['uri'] . ', ' . var_export($options, true) . ') ?>';
    }
}
