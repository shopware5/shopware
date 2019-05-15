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
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
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
             . '$mediaService = Shopware()->Container()->get(\'shopware_media.media_service\'); '
             . 'echo $mediaService->getUrl(' . $_attr['path'] . '); ?>';
    }
}
