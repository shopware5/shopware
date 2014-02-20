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

namespace Shopware\Components\Thumbnail;

use Shopware\Components\Thumbnail\Generator\GeneratorInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Shopware Thumbnail Manager
 *
 * This class handles the generation of thumbnails.
 * It uses a passed thumbnail generator which will be used for creating the thumbnails.
 * It expects a passed media object for further information handling.
 *
 * Class Manager
 * @category    Shopware
 * @package     Shopware\Components\Thumbnail
 * @copyright   Copyright (c) shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * This generator will be used for the thumbnail creation itself
     *
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var String
     */
    protected $rootDir;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * The constructor for the thumbnail manager.
     * Expects a passed generator and the media/destination directory
     *
     * @param GeneratorInterface $generator
     * @param String $rootDir - the full path to the shopware directory e.g. /var/www/shopware/
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(GeneratorInterface $generator, $rootDir, \Enlight_Event_EventManager $eventManager)
    {
        $this->generator = $generator;
        $this->rootDir = $rootDir;
        $this->eventManager = $eventManager;
    }

    /**
     * Method to generate a single thumbnail.
     * First it loads an image from the media path,
     * then resizes it and saves it to the default thumbnail directory
     *
     * @param Media $media
     * @param array $thumbnailSizes - array of all sizes which needs to be generated
     * @param bool $keepProportions - Whether or not keeping the proportions of the original image, the size can be affected when true
     * @throws \Exception
     * @return bool
     */
    public function createMediaThumbnail(Media $media, $thumbnailSizes = array(), $keepProportions = false)
    {
        if ($media->getType() !== $media::TYPE_IMAGE) {
            throw new \Exception("File is not an image.");
        }

        if (!is_writable($this->getThumbnailDir($media))) {
            throw new \Exception("Thumbnail directory is not writable");
        }

        if (empty($thumbnailSizes)) {
            $album = $media->getAlbum();

            if (!$album) {
                throw new \Exception("No album configured for the passed media object and no size passed!");
            }

            $settings = $album->getSettings();

            if (!$settings) {
                throw new \Exception("No settings configured in the album of the given media object!");
            }

            $settingSizes = $settings->getThumbnailSize();

            if (empty($settingSizes) || empty($settingSizes[0])) {
                throw new \Exception("No thumbnail sizes were found in the album settings");
            }

            $thumbnailSizes = array_merge($thumbnailSizes, $album->getSettings()->getThumbnailSize());
        }

        $thumbnailSizes = array_merge($thumbnailSizes, $media->getDefaultThumbnails());

        $thumbnailSizes = $this->uniformThumbnailSizes($thumbnailSizes);

        $imagePath = $this->rootDir . DIRECTORY_SEPARATOR . $media->getPath();

        $parameters = array(
            'path' => $imagePath,
            'sizes' => $thumbnailSizes,
            'keepProportions' => $keepProportions
        );

        if ($this->eventManager) {
            $parameters = $this->eventManager->filter(
                'Shopware_Components_Thumbnail_Manager_CreateThumbnail',
                $parameters,
                array('subject' => $this, 'media' => $media)
            );
        }

        foreach ($parameters['sizes'] as $size) {
            $suffix = $size['width'] . 'x' . $size['height'];

            $destinations = $this->getDestination($media, $suffix);

            foreach($destinations as $destination){

                $this->generator->createThumbnail(
                    $parameters['path'],
                    $destination,
                    $size['width'],
                    $size['height'],
                    $parameters['keepProportions']
                );
            }
        }
    }

    /**
     * Returns an array with a jpg and original extension path if its not a jpg
     *
     * @param Media $media
     * @param $suffix
     * @return array
     * @throws \Exception
     */
    protected function getDestination(Media $media, $suffix)
    {
        $thumbnailDir = $this->getThumbnailDir($media);

        $fileNames = array(
            'jpg' => $thumbnailDir . $media->getName() . '_' . $suffix . '.jpg'
        );

        if ($media->getExtension() !== 'jpg') {
            $fileNames[$media->getExtension()] = $thumbnailDir. $media->getName() . '_' . $suffix . '.' . $media->getExtension();
        }

        return $fileNames;
    }

    /**
     * Returns the full path of a thumbnail dir according to the media type
     * The default path for images after the root dir would be media/image/thumbnail/
     *
     * @param $media
     * @return string
     */
    protected function getThumbnailDir($media)
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . strtolower($media->getType()) . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR;
    }

    /**
     * Brings the passed sizes into a uniform format.
     *
     * The passed param has to be an array with one or more sizes.
     * These sizes can have following formats:
     *
     * '100x110'
     * array(120, 130)
     * array(140)
     * array('width' => 150, 'height' => 160)
     *
     * The method returns an array of sizes with following format:
     *
     * array('width' => 100, 'height' => 200)
     *
     * @param $thumbnailSizes
     * @return array
     */
    protected function uniformThumbnailSizes($thumbnailSizes)
    {
        foreach ($thumbnailSizes as &$size) {
            if (is_string($size)) {
                if (strpos($size, 'x') !== false) {
                    $size = $this->stringSizeToArray($size);
                } else {
                    $size = array('width' => $size, 'height' => $size);
                }
            } else {
                if (is_array($size) && !array_key_exists('width', $size)) {
                    $size = array('width' => $size[0], 'height' => isset($size[1]) ? $size[1] : $size[0]);
                }

                if (is_int($size)) {
                    $size = array('width' => $size[0], 'height' => isset($size[1]) ? $size[1] : $size[0]);
                }
            }
        }

        return $thumbnailSizes;
    }

    /**
     * Returns an array with width and height gained
     * from a string in a format like 100x200
     *
     * @param $size
     * @return array
     */
    private function stringSizeToArray($size)
    {
        $size = explode('x', $size);

        return array('width' => $size[0], 'height' => $size[1]);
    }

    /**
     * Deletes all thumbnails from the given media object
     *
     * @param Media $media
     */
    public function removeMediaThumbnails(Media $media)
    {
        $thumbnails = $media->getThumbnailFilePaths();

        foreach ($thumbnails as $thumbnail) {
            $thumbnailPath = $this->rootDir . DIRECTORY_SEPARATOR . $thumbnail;

            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
}
