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

/**
 * Interface MediaServiceInterface
 * @package Shopware\Bundle\MediaBundle
 */
interface MediaServiceInterface
{
    /**
     * Get media path including configured mediaUrl
     *
     * @param string $path
     * @return string|null
     */
    public function getUrl($path);

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return string|false The file contents or false on failure.
     */
    public function read($path);

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @return resource|false The path resource or false on failure.
     */
    public function readStream($path);

    /**
     * Create a file or update if exists using a string as content.
     *
     * @param string $path The path to the file.
     * @param string $contents The file contents.
     * @param bool $append Append file
     *
     * @return bool True on success, false on failure.
     */
    public function write($path, $contents, $append = false);

    /**
     * Write a new file using a stream.
     *
     * @param string $path The path of the new file.
     * @param resource $resource The file handle.
     * @param bool $append Append file
     *
     * @return bool True on success, false on failure.
     */
    public function writeStream($path, $resource, $append = false);

    /**
     * List files of the file system
     *
     * @param string $directory
     * @return array A list of file paths.
     */
    public function listFiles($directory = '');

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool   $recursive Whether to list recursively.
     *
     * @return array A list of file metadata.
     */
    public function listContents($directory = '', $recursive = false);

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path);

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path);

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize($path);

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath);

    /**
     * Normalizes the path based on the configured strategy
     *
     * @param string $path
     * @return string
     */
    public function normalize($path);

    /**
     * Builds the path on the filesystem
     *
     * @param string $path
     * @return string
     */
    public function encode($path);

    /**
     * Checks if the provided path matches the algorithm format
     *
     * @param string $path
     * @return string
     */
    public function isEncoded($path);

    /**
     * Returns the adapter type. e.g. 'local'
     *
     * @return string
     */
    public function getAdapterType();

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     *
     * @return bool True on success, false on failure.
     */
    public function createDir($dirname);

    /**
     * Migrates a file to the new strategy if it's not present
     *
     * @param string $path
     * @return bool
     */
    public function migrateFile($path);
}
