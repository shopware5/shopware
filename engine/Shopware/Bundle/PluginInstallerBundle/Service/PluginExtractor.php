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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Symfony\Component\Filesystem\Filesystem;

class PluginExtractor
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param string $pluginDir
     * @param Filesystem $filesystem
     */
    public function __construct($pluginDir, Filesystem $filesystem)
    {
        $this->pluginDir = $pluginDir;
        $this->filesystem = $filesystem;
    }

    /**
     * Extracts the provided zip file to the provided destination
     *
     * @param \ZipArchive $archive
     * @throws \Exception
     */
    public function extract($archive)
    {
        $destination = $this->pluginDir;

        if (!is_writable($destination)) {
            throw new \Exception(
                'Destination directory is not writable'
            );
        }

        $prefix = $this->getPluginPrefix($archive);
        $this->validatePluginZip($prefix, $archive);

        $pluginDir = $destination.'/'.$prefix;

        $oldFile = false;
        if ($this->filesystem->exists($pluginDir)) {
            $oldFile = $pluginDir.'.'.uniqid();
            $this->filesystem->rename($pluginDir, $oldFile);
            rename($pluginDir, $oldFile);
        }

        try {
            $archive->extractTo($destination);

            if ($oldFile) {
                $this->filesystem->remove($oldFile);
            }
        } catch (\Exception $e) {
            if ($oldFile) {
                $this->filesystem->rename($oldFile, $pluginDir);
            }
            throw $e;
        }

        $this->clearOpcodeCache();
    }

    /**
     * Iterates all files of the provided zip archive
     * path and validates the plugin namespace, directory traversal
     * and multiple plugin directories.
     *
     * @param string $prefix
     * @param \ZipArchive $archive
     */
    private function validatePluginZip($prefix, \ZipArchive $archive)
    {
        for ($i = 2; $i < $archive->numFiles; $i++) {
            $stat = $archive->statIndex($i);

            $this->assertNoDirectoryTraversal($stat['name']);
            $this->assertPrefix($stat['name'], $prefix);
        }
    }

    /**
     * @param \ZipArchive $archive
     * @return string
     */
    private function getPluginPrefix(\ZipArchive $archive)
    {
        $entry = $archive->statIndex(0);

        $pluginName = rtrim($entry['name'], '/');

        return $pluginName;
    }

    /**
     * Clear opcode caches to make sure that the
     * updated plugin files are used in the following requests.
     */
    private function clearOpcodeCache()
    {
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }

    /**
     * @param string $filename
     * @param string $prefix
     */
    private function assertPrefix($filename, $prefix)
    {
        if (strpos($filename, $prefix) !== 0) {
            throw new \RuntimeException(
                sprintf(
                    'Detected invalid file/directory %s in the plugin zip: %s',
                    $filename,
                    $prefix
                )
            );
        }
    }

    /**
     * @param $filename
     */
    private function assertNoDirectoryTraversal($filename)
    {
        if (strpos($filename, '../') !== false) {
            throw new \RuntimeException(
                sprintf('Directory Traversal detected')
            );
        }
    }
}
