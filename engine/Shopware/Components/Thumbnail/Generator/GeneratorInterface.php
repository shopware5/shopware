<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License and of our
 * proprietary license can be found at and
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers, Shopware_Models
 * @subpackage Backend, Frontend, Article, Adapter
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $id$
 * @author     Shopware
 */

namespace Shopware\Components\Thumbnail\Generator;

/**
 * Interface for a thumbnail generator
 *
 * To write an own generator to use it for the thumbnail manager
 * you have to implement this interface.
 *
 * To create a new thumbnail you have to implement
 * and call the createThumbnail function which takes
 * the image path, destination and size of the thumbnail
 *
 * Class GeneratorInterface
 * @package Shopware\Components\Thumbnail\Generator
 */
interface GeneratorInterface
{
    /**
     * This function creates a thumbnail from the given image path
     * and saves it to the defined destination with the given size
     *
     * @param $image - original image path
     * @param $destination - full path of the generated thumbnail
     * @param $width - width in pixel
     * @param $height - height in pixel
     * @return mixed
     */
    public function createThumbnail($image, $destination, $width, $height);
}