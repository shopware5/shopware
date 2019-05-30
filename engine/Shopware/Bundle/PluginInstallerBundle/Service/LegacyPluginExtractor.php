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

class LegacyPluginExtractor
{
    /**
     * Extracts the provided zip file to the provided destination
     *
     * @param \ZipArchive $archive
     * @param string      $destination
     *
     * @throws \Exception
     */
    public function extract($archive, $destination)
    {
        if (!is_writable($destination)) {
            throw new \Exception(sprintf('Destination directory "%s" is not writable', $destination));
        }

        $this->validatePluginZip($archive);

        $archive->extractTo($destination);

        $this->clearOpcodeCache();

        unlink($archive->filename);
    }

    /**
     * Iterates all files of the provided zip archive
     * path and validates the plugin namespace, directory traversal
     * and multiple plugin directories.
     *
     * @throws \Exception
     */
    private function validatePluginZip(\ZipArchive $archive)
    {
        $prefix = $this->getLegacyPluginPrefix($archive);

        $this->assertValid($archive, $prefix);
    }

    /**
     * @param string $prefix
     */
    private function assertValid(\ZipArchive $archive, $prefix)
    {
        for ($i = 2; $i < $archive->numFiles; ++$i) {
            $stat = $archive->statIndex($i);

            $this->assertNoDirectoryTraversal($stat['name']);
            $this->assertPrefix($stat['name'], $prefix);
        }
    }

    /**
     * @return string
     */
    private function getLegacyPluginPrefix(\ZipArchive $archive)
    {
        $segments = $archive->statIndex(0);
        $segments = array_filter(explode('/', $segments['name']));

        if (!in_array($segments[0], ['Frontend', 'Backend', 'Core'])) {
            throw new \RuntimeException('Uploaded zip archive contains no plugin namespace directory');
        }

        if (count($segments) <= 1) {
            $segments = $archive->statIndex(1);
            $segments = array_filter(explode('/', $segments['name']));
        }

        return implode('/', [$segments[0], $segments[1]]);
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
     * @param string $filename
     */
    private function assertNoDirectoryTraversal($filename)
    {
        if (strpos($filename, '../') !== false) {
            throw new \RuntimeException('Directory Traversal detected');
        }
    }
}
