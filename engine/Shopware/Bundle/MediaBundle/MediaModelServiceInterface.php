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

namespace Shopware\Bundle\MediaBundle;

use Shopware\Models\Media\Media;

interface MediaModelServiceInterface
{
    /**
     * Returns an array of all thumbnail paths the media object can have
     *
     * @param bool $highDpi - If true, returns the file path for the high dpi thumbnails instead
     */
    public function getThumbnailFilePaths(Media $media, bool $highDpi = false, array $initialSizes = []): array;

    /**
     * Removes special characters from a filename
     */
    public function removeSpecialCharacters(string $name): string;

    /**
     * Returns the directory to upload
     */
    public function getUploadDir(string $type): string;

    /**
     * Returns the directory of the thumbnail files.
     */
    public function getThumbnailDir(string $type): string;

    /**
     * Create a thumbnail file for the internal file with the passed width and height.
     */
    public function createThumbnail(Media $media, int $width, int $height): void;

    /**
     * Creates the default thumbnails 70x70 and 153x153 to display the images
     * in the media manager listing.
     */
    public function createDefaultThumbnails(Media $media): void;

    /**
     * Internal helper function which updates all associated data which has the image path as own property.
     */
    public function updateAssociations(Media $media);

    /**
     * Removes the default thumbnail files. The file name have to be passed, because on update the internal
     * file name property is already changed to the new name.
     */
    public function removeDefaultThumbnails(Media $media): void;

    /**
     * Searches all album settings for thumbnail sizes
     */
    public function getAllThumbnailSizes(): array;

    /**
     * Moves the uploaded file to the correctly directory.
     */
    public function uploadFile(Media $media): bool;

    /**
     * Create the new names for the jpg file and the file with the original extension
     * Also returns high dpi paths
     */
    public function getThumbnailNames(Media $media, string $suffix): array;

    /**
     * Extract the file information from the uploaded file, into the internal properties
     */
    public function setFileInfo(Media $media): void;
}
