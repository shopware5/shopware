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

interface MediaBackendInterface
{
    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path);

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     * @return string|false The file contents or false on failure.
     */
    public function read($path);

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool $recursive Whether to list recursively.
     *
     * @return array A list of file metadata.
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Write a new file.
     *
     * @param string $path The path of the new file.
     * @param string $contents The file contents.
     *
     * @return bool True on success, false on failure.
     */
    public function write($path, $contents);

    /**
     * Rename a file.
     *
     * @param string $path Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @return bool
     */
    public function rename($path, $newpath);

    /**
     * Delete a file.
     *
     * @param string $path
     * @return bool True on success, false on failure.
     */
    public function delete($path);

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     *
     * @return bool True on success, false on failure.
     */
    public function createDir($dirname);

    /**
     * @return string
     */
    public function getMediaUrl();

    /**
     * Applies the backend algorithm to the given path
     *
     * @param string $path E.g. media/image/my-awesome-image.png
     * @return string
     */
    public function toUrlPath($path);

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize($path);

    /**
     * Returns the adapter type
     *
     * @return string
     */
    public function getAdapterType();

    /**
     * Checks if the provided path matches the algorithm format
     *
     * @param string $path
     * @return bool
     */
    public function isRealPathFormat($path);
}
