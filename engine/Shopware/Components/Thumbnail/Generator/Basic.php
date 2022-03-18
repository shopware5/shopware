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

use RuntimeException;
use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;
use Shopware_Components_Config;

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

    public function __construct(Shopware_Components_Config $config, MediaServiceInterface $mediaService, OptimizerServiceInterface $optimizerService)
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
        $maxWidth = (int) $maxWidth;
        $maxHeight = (int) $maxHeight;
        $quality = (int) $quality;

        if (!$this->mediaService->has($imagePath)) {
            throw new RuntimeException(sprintf('File not found: %s', $imagePath));
        }

        $content = $this->mediaService->read($imagePath);
        if (!\is_string($content)) {
            throw new RuntimeException(sprintf('Could not read image from file: %s', $imagePath));
        }
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
     * @return array{width: int, height: int}
     */
    private function getOriginalImageSize($imageResource): array
    {
        return [
            'width' => (int) imagesx($imageResource),
            'height' => (int) imagesy($imageResource),
        ];
    }

    /**
     * Determines the extension of the file according to
     * the given path and calls the right creation
     * method for the image extension
     *
     * @throws RuntimeException
     *
     * @return resource
     */
    private function createImageResource(string $fileContent, string $imagePath)
    {
        $image = imagecreatefromstring($fileContent);
        if ($image === false) {
            throw new RuntimeException(sprintf('Image is not in a recognized format (%s)', $imagePath));
        }

        return $image;
    }

    /**
     * Returns the extension of the file with passed path
     */
    private function getImageExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Calculate image proportion and set the new resolution
     *
     * @param array{width: int, height: int} $originalSize
     *
     * @return array{width: int, height: int, proportion: float}
     */
    private function calculateProportionalThumbnailSize(array $originalSize, int $width, int $height): array
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
            'width' => (int) $dstWidth,
            'height' => (int) $dstHeight,
            'proportion' => $factor,
        ];
    }

    /**
     * @param resource                       $image
     * @param array{width: int, height: int} $originalSize
     * @param array{width: int, height: int} $newSize
     *
     * @return resource
     */
    private function createNewImage($image, array $originalSize, array $newSize, string $extension)
    {
        // Creates a new image with given size
        $newImage = imagecreatetruecolor($newSize['width'], $newSize['height']);
        if ($newImage === false) {
            throw new RuntimeException('Could not create image');
        }

        if (\in_array($extension, ['jpg', 'jpeg'])) {
            $background = (int) imagecolorallocate($newImage, 255, 255, 255);
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
     * @param array{width: int, height: int} $newSize
     * @param resource                       $newImage
     */
    private function fixGdImageBlur(array $newSize, $newImage): void
    {
        $colorWhite = (int) imagecolorallocate($newImage, 255, 255, 255);
        $processHeight = $newSize['height'] + 0;
        $processWidth = $newSize['width'] + 0;
        for ($y = 0; $y < $processHeight; ++$y) {
            for ($x = 0; $x < $processWidth; ++$x) {
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
     * @param resource $newImage
     * @param int      $quality  - JPEG quality
     */
    private function saveImage(string $destination, $newImage, int $quality): void
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
        if (!\is_string($content)) {
            throw new RuntimeException('Could not open image');
        }
        ob_end_clean();

        $this->mediaService->write($destination, $content);
    }

    private function optimizeImage(string $destination): void
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

    private function downloadImage(string $destination): string
    {
        $tmpFilename = tempnam(sys_get_temp_dir(), 'optimize_image');
        if (!\is_string($tmpFilename)) {
            throw new RuntimeException('Could not create tmp file name');
        }
        $handle = fopen($tmpFilename, 'wb');
        if (!\is_resource($handle)) {
            throw new RuntimeException(sprintf('Could not open file at: %s', $tmpFilename));
        }

        $fromHandle = $this->mediaService->readStream($destination);
        if (!\is_resource($fromHandle)) {
            throw new RuntimeException(sprintf('Could not open file at: %s', $destination));
        }
        stream_copy_to_stream(
            $fromHandle,
            $handle
        );

        return $tmpFilename;
    }

    private function uploadImage(string $destination, string $tmpFilename): void
    {
        $fileHandle = fopen($tmpFilename, 'rb');
        if (!\is_resource($fileHandle)) {
            throw new RuntimeException(sprintf('Could not open file at: %s', $tmpFilename));
        }
        $this->mediaService->writeStream($destination, $fileHandle);
        fclose($fileHandle);
    }
}
