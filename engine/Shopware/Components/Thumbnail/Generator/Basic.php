<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Components\Thumbnail\Generator;

/**
 * Shopware Basic Thumbnail Generator
 *
 * This is a generator which creates image objects
 * based on the passed image path which will be used
 * for further manipulation.
 *
 * Class Basic
 * @category    Shopware
 * @package     Shopware\Component\Thumbnail\Generator
 * @copyright   Copyright (c) shopware AG (http://www.shopware.de)
 */
class Basic implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function createThumbnail($imagePath, $destination, $width, $height, $keepProportions = false)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("File not found: " . $imagePath);
        }

        $image = $this->createImageResource($imagePath);

        // Determines the width and height of the original image
        $originalSize = $this->getOriginalImageSize($image);

        if (empty($height)) {
            $height = $width;
        }

        $newSize = array(
            'width'  => $width,
            'height' => $height
        );

        if ($keepProportions === true){
            $newSize = $this->calculateProportionalThumbnailSize($originalSize, $width, $height);
        }

        $newImage = $this->createNewImage($image, $originalSize, $newSize);
        $this->saveImage($destination, $newImage);

        // Removes both the original and the new created image from memory
        imagedestroy($newImage);
        imagedestroy($image);
    }

    /**
     * Returns an array with a width and height index
     * according to the passed sizes
     *
     * @param resource $imageResource
     * @return array
     */
    private function getOriginalImageSize($imageResource)
    {
        return array(
            'width'  => imagesx($imageResource),
            'height' => imagesy($imageResource)
        );
    }

    /**
     * Determines the extension of the file according to
     * the given path and calls the right creation
     * method for the image extension
     *
     * @param string $path
     * @return resource
     * @throws \RuntimeException
     */
    private function createImageResource($path)
    {
        if (!$image = @imagecreatefromstring(file_get_contents($path))) {
            throw new \RuntimeException(sprintf("Image is not in a recognized format (%s)", $path));
        }

        return $image;
    }

    /**
     * Returns the extension of the file with passed path
     *
     * @param string
     * @return string
     */
    private function getImageExtension($path)
    {
        $pathInfo = pathinfo($path);

        return $pathInfo['extension'];
    }

    /**
     * Calculate image proportion and set the new resolution
     *
     * @param array $originalSize
     * @param int   $width
     * @param int   $height
     * @return array
     */
    private function calculateProportionalThumbnailSize(array $originalSize, $width, $height)
    {
        // Source image size
        $srcWidth = $originalSize['width'];
        $srcHeight = $originalSize['height'];

        // Calculate the scale factor
        if ($width === 0) {
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

    /**
     * @param resource $image
     * @param array    $originalSize
     * @param array    $newSize
     * @return resource
     */
    private function createNewImage($image, $originalSize, $newSize)
    {
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

        return $newImage;
    }

    /**
     * @param string $destination
     * @param resource $newImage
     */
    private function saveImage($destination, $newImage)
    {
        // saves the image information into a specific file extension
        switch (strtolower($this->getImageExtension($destination))) {
            case 'png':
                imagepng($newImage, $destination);
                break;
            case 'gif':
                imagegif($newImage, $destination);
                break;
            default:
                imagejpeg($newImage, $destination, 90);
                break;
        }
    }
}
