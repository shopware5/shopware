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
 * Shopware Basic Thumbnail Generator
 *
 * This is a generator which creates image objects
 * based on the passed image path which will be used
 * for further manipulation.
 *
 * Class Basic
 * @package Shopware\Component\Thumbnail\Generator
 */
class Basic implements GeneratorInterface
{
    /**
     * This method creates a new thumbnail based on the given parameters
     *
     * @param $imagePath
     * @param $destination
     * @param $width
     * @param $height
     * @param bool $keepProportions
     * @throws \Exception
     * @return void
     */
    public function createThumbnail($imagePath, $destination, $width, $height, $keepProportions = false)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("File not found: " . $imagePath);
        }

        // Saves image data to memory for usage
        $image = $this->createFileImage($imagePath);

        if($image === false){
            throw new \Exception("Image could not be created: " . $imagePath);
        }

        // Determines the width and height of the original image
        $originalSize = $this->getOriginalImageSize($imagePath);

        if (empty($height)) {
            $height = $width;
        }

        $newSize = array('width' => $width, 'height' => $height);

        if($keepProportions !== false){
            $newSize = $this->calculateProportionalThumbnailSize($originalSize, $width, $height);
        }

        // Creates a new image with given size
        $newImage = imagecreatetruecolor($newSize['width'], $newSize['height']);

        // Disables blending
        imagealphablending($newImage, false);
        // Saves the alpha informations
        imagesavealpha($newImage, true);
        // Copies the original image into the new created image with resampling
        imagecopyresampled(
            $newImage,
            $image,
            0,
            0,
            0,
            0,
            $newSize['width'],
            $newSize['height'],
            $originalSize['width'],
            $originalSize['height']
        );

        // saves the image information into a jpg file with a quality rate of 90%
        imagejpeg($newImage, $destination, 90);

        // Removes both the original and the new created image from memory
        imagedestroy($newImage);
        imagedestroy($image);
    }

    /**
     * Returns an array with a width and height index
     * according to the passed sizes
     *
     * @param $path
     * @return array
     */
    private function getOriginalImageSize($path)
    {
        $size = getimagesize($path);

        return array('width' => $size[0], 'height' => $size[1]);
    }

    /**
     * Determines the extension of the file according to
     * the given path and calls the right creation
     * method for the image extension
     *
     * @param $path
     * @return bool|resource
     * @throws \Exception
     */
    private function createFileImage($path)
    {
        // Determines the image creation by the file extension
        switch (strtolower($this->getImageExtension($path))) {
            case 'gif':
                $image = imagecreatefromgif($path);
                break;
            case 'png':
                $image = imagecreatefrompng($path);
                break;
            case 'jpg':
                $image = imagecreatefromjpeg($path);
                break;
            default:
                throw new \Exception("Extension is not supported");
        }

        return $image;
    }

    /**
     * Returns the extension of the file with passed path
     *
     * @param $path
     * @return mixed
     */
    private function getImageExtension($path)
    {
        $pathInfo = pathinfo($path);
        return $pathInfo['extension'];
    }

    /**
     * Calculate image proportion and set the new resolution
     * @param $originalSize
     * @param $width
     * @param $height
     * @return array
     */
    private function calculateProportionalThumbnailSize(array $originalSize, $width, $height)
    {
        // Source image size
        $srcWidth = $originalSize['width'];
        $srcHeight = $originalSize['height'];

        // Calculate the scale factor
        if($width === 0) {
            $factor = $height / $srcHeight;
        } else if($height === 0) {
            $factor = $width / $srcWidth;
        } else {
            $factor = min($width / $srcWidth, $height / $srcHeight);
        }

        // Get the destination size
        $dstWidth = round($srcWidth * $factor);
        $dstHeight = round($srcHeight * $factor);

        return array(
            'width' => $dstWidth,
            'height' => $dstHeight,
            'proportion' => $factor
        );
    }
}