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
 * Returns the path to an image by its media-id
 * Params:
 * - id:   media-id
 * - size: thumbnail-size (returns org-image if omitted)
 */
class Smarty_Compiler_MediaById extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = array('id');

    /**
     * Array of names of valid option flags
     *
     * @var array
     */
    public $option_flags = array('size');

    /**
     * @param $args
     * @param $compiler
     * @return string
     */
    public function compile($args, $compiler)
    {
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        return '<?php '
             . '$id = ' . $_attr['id'] . ';'
             . '$size = ' . ($_attr['size'] ?: 'false') . ';'
             . '$mediaService = Shopware()->Container()->get(\'shopware_media.media_service\');'
             . '$mediaRepo = Shopware()->Models()->getRepository(\'Shopware\Models\Media\Media\');'
             . '$mediaObject = $mediaRepo->find($id);'
             . 'if ($mediaObject instanceof \Shopware\Models\Media\Media) {'
                 . '$thumbnails = $mediaObject->getThumbnails();'
                 . 'if ($size && array_key_exists($size, $thumbnails)) {'
                     . 'echo $thumbnails[$size];'
                 . '} else {'
                     . 'echo $mediaService->getUrl($mediaObject->getPath());'
                 . '}'
             . '} else {'
                 . 'echo $id;'
             . '}'
             . '?>';
    }
}
