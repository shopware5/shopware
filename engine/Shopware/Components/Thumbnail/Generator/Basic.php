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

namespace Shopware\Components\Thumbnail\Generator;

use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;

/**
 * Shopware Basic Thumbnail Generator
 *
 * This is a generator which creates image objects
 * based on the passed image path which will be used
 * for further manipulation.
 */
class Basic implements GeneratorInterface
{
    /**
     * @var bool
     */
    private $fixGdImageBlur;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var OptimizerServiceInterface
     */
    private $optimizerService;

    public function __construct(\Shopware_Components_Config $config, MediaServiceInterface $mediaService, OptimizerServiceInterface $optimizerService)
    {
        $this->fixGdImageBlur = $config->get('thumbnailNoiseFilter');
        $this->mediaService = $mediaService;
        $this->optimizerService = $optimizerService;
    }

    /**
     * {@inheritdoc}
     */
    public function createThumbnail($imagePath, $destination, $maxWidth, $maxHeight, $keepProportions = false, $quality = 90)
    {
        if (!$this->mediaService->has($imagePath)) {
            throw new \Exception(sprintf('File not found: %s', $imagePath));
        }

        $content = $this->mediaService->read($imagePath);
        $image = $this->createImageResource($content, $imagePath);

        // Determines the width and height of the original image
        $originalSize = $this->getOriginalImageSize($image);

        if (empty($maxHeight)) {
            $maxHeight = $maxWidth;
        }

        $newSize = [
            'width' => $maxWidth,
            'height' => $maxHeight,
        ];

        if ($keepProportions) {
            $newSize = $this->calculateProportionalThumbnailSize($originalSize, $maxWidth, $maxHeight);
        }

        $newImage = $this->createNewImage($image, $originalSize, $newSize, $this->getImageExtension($destination));

        if ($this->fixGdImageBlur) {
            $this->fixGdImageBlur($newSize, $newImage);
        }

        $this->saveImage($destination, $newImage, $quality);
        $this->optimizeImage($destination);

        // Removes both the original and the new created image from memory
        imagedestroy($newImage);
        imagedestroy($image);
    }

    /**
     * Returns an array with a width and height index
     * according to the passed sizes
     *
     * @param resource $imageResource
     *
     * @return array
     */
    private function getOriginalImageSize($imageResource)
    {
        return [
            'width' => imagesx($imageResource),
            'height' => imagesy($imageResource),
        ];
    }

    /**
     * Determines the extension of the file according to
     * the given path and calls the right creation
     * method for the image extension
     *
     * @param string $fileContent
     * @param string $imagePath
     *
     * @throws \RuntimeException
     *
     * @return resource
     */
    private function createImageResource($fileContent, $imagePath)
    {
        if (!$image = @imagecreatefromstring($fileContent)) {
            throw new \RuntimeException(sprintf('Image is not in a recognized format (%s)', $imagePath));
        }

        return $image;
    }

    /**
     * Returns the extension of the file with passed path
     *
     * @param string $path
     *
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
     * @param int $width
     * @param int $height
     *
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
        } elseif ($height === 0) {
            $factor = $width / $srcWidth;
        } else {
            $factor = min($width / $srcWidth, $height / $srcHeight);
        }

        if ($factor >= 1) {
            $dstWidth = $srcWidth;
            $dstHeight = $srcHeight;
            $factor = 1;
        } else {
            //Get the destination size
            $dstWidth = round($srcWidth * $factor);
            $dstHeight = round($srcHeight * $factor);
        }

        return [
            'width' => $dstWidth,
            'height' => $dstHeight,
            'proportion' => $factor,
        ];
    }

    /**
     * @param resource $image
     * @param array    $originalSize
     * @param array    $newSize
     * @param string   $extension
     *
     * @return resource
     */
    private function createNewImage($image, $originalSize, $newSize, $extension)
    {
        // Creates a new image with given size
        $newImage = imagecreatetruecolor($newSize['width'], $newSize['height']);

        if (in_array($extension, ['jpg', 'jpeg'])) {
            $background = imagecolorallocate($newImage, 255, 255, 255);
            imagefill($newImage, 0, 0, $background);
        } else {
            // Disables blending
            imagealphablending($newImage, false);
        }
        // Saves the alpha information
        imagesavealpha($newImage, true);
        // Copies the original image into the new created image with re-sampling
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
     * Fix #fefefe in white backgrounds
     *
     * @param array    $newSize
     * @param resource $newImage
     */
    private function fixGdImageBlur($newSize, $newImage)
    {
        $colorWhite = imagecolorallocate($newImage, 255, 255, 255);
        $processHeight = $newSize['height'] + 0;
        $processWidth = $newSize['width'] + 0;
        for ($y = 0; $y < ($processHeight); ++$y) {
            for ($x = 0; $x < ($processWidth); ++$x) {
                $colorat = imagecolorat($newImage, $x, $y);
                $r = ($colorat >> 16) & 0xFF;
                $g = ($colorat >> 8) & 0xFF;
                $b = $colorat & 0xFF;
                if (($r == 253 && $g == 253 && $b == 253) || ($r == 254 && $g == 254 && $b == 254)) {
                    imagesetpixel($newImage, $x, $y, $colorWhite);
                }
            }
        }
    }

    /**
     * @param string   $destination
     * @param resource $newImage
     * @param int      $quality     - JPEG quality
     */
    private function saveImage($destination, $newImage, $quality)
    {
        ob_start();
        // saves the image information into a specific file extension
        switch (strtolower($this->getImageExtension($destination))) {
            case 'png':
                imagepng($newImage);
                break;
            case 'gif':
                imagegif($newImage);
                break;
            default:
                imagejpeg($newImage, null, $quality);
                break;
        }

        $content = ob_get_contents();
        ob_end_clean();

        $this->mediaService->write($destination, $content);
    }

    /**
     * @param string $destination
     */
    private function optimizeImage($destination)
    {
        $tmpFilename = $this->downloadImage($destination);

        try {
            $this->optimizerService->optimize($tmpFilename);
            $this->uploadImage($destination, $tmpFilename);
        } catch (OptimizerNotFoundException $exception) {
            // empty catch intended since no optimizer is available
        } finally {
            unlink($tmpFilename);
        }
    }

    /**
     * @param string $destination
     *
     * @return string
     */
    private function downloadImage($destination)
    {
        $tmpFilename = tempnam(sys_get_temp_dir(), 'optimize_image');
        $handle = fopen($tmpFilename, 'wb');

        stream_copy_to_stream(
            $this->mediaService->readStream($destination),
            $handle
        );

        return $tmpFilename;
    }

    /**
     * @param string $destination
     * @param string $tmpFilename
     */
    private function uploadImage($destination, $tmpFilename)
    {
        $fileHandle = fopen($tmpFilename, 'rb');
        $this->mediaService->writeStream($destination, $fileHandle);
        fclose($fileHandle);
    }
}
