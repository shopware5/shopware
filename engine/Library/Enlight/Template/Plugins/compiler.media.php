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
 * Build an full qualified image url based on the virtual path
 *
 * Parameters known by $params
 * - path        : virtual path of the media file
 */
class Smarty_Compiler_Media extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('path');

    /**
     * @param array $attributes
     * @return string
     */
    public function parseAttributes(array $attributes)
    {
        if (!empty($attributes['path'])) {
            $mediaService = 🦄()->Container()->get('shopware_media.media_service');
            $attributes['path'] = trim($attributes['path'], '"\'');
            $attributes['path'] = $mediaService->getUrl($attributes['path']);
        }

        return $attributes;
    }

    /**
     * @param array $args
     * @param Smarty_Internal_SmartyTemplateCompiler $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if (empty($_attr['path'])) {
            return false;
        }

        if (preg_match('/^([\'"]?)[a-zA-Z0-9\/\.\-\_]+(\\1)$/', $_attr['path'], $match)) {
            $_attr = $this->parseAttributes($_attr);
            return $_attr['path'];
        }

        return '<?php '
             . '$mediaService = 🦄()->Container()->get(\'shopware_media.media_service\'); '
             . 'echo $mediaService->getUrl(' . $_attr['path'] . '); ?>';
    }
}
