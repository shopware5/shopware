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
    public $optional_attributes = ['id', 'path'];

    /**
     * @param array $attributes
     * @return string
     */
    public function parseAttributes(array $attributes)
    {
        if (!empty($attributes['path'])) {
            /** @var Shopware\Bundle\MediaBundle\MediaService $mediaService */
            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            $attributes['path'] = trim($attributes['path'], '"\'');
            $attributes['path'] = $mediaService->getUrl($attributes['path']);
        }

        return $attributes;
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function getImagePath(array $attributes)
    {
        $path = Shopware()->Db()->query('SELECT path FROM s_media WHERE id = ?', $attributes['id'])->fetchColumn();

        if ($path) {
            $attributes['path'] = $path;
            $attributes = $this->parseAttributes($attributes);
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

        if (empty($_attr['path']) && empty($_attr['id'])) {
            return false;
        }

        if (is_numeric($_attr['id'])) {
            $_attr = $this->getImagePath($_attr);
            return $_attr['path'];
        }

        if (preg_match('/^([\'"]?)[a-zA-Z0-9\/\.\-\_]+(\\1)$/', $_attr['path'], $match)) {
            $_attr = $this->parseAttributes($_attr);
            return $_attr['path'];
        }

        if (!empty($_attr['id'])) {
            return '<?php '
                . '$path = Shopware()->Db()->query(\'SELECT path FROM s_media WHERE id = ?\', ' . $_attr['id'] . ')->fetchColumn();'
                . '$mediaService = Shopware()->Container()->get(\'shopware_media.media_service\'); '
                . 'echo $mediaService->getUrl($path); ?>';
        }

        return '<?php '
            . '$mediaService = Shopware()->Container()->get(\'shopware_media.media_service\'); '
            . 'echo $mediaService->getUrl(' . $_attr['path'] . '); ?>';
    }
}
