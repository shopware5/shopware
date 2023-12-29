<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Exception;
use RuntimeException;
use ZipArchive;

class LegacyPluginExtractor
{
    /**
     * Extracts the provided zip file to the provided destination
     *
     * @param ZipArchive $archive
     * @param string     $destination
     *
     * @throws Exception
     *
     * @return void
     */
    public function extract($archive, $destination)
    {
        if (!is_writable($destination)) {
            throw new Exception(sprintf('Destination directory "%s" is not writable', $destination));
        }

        $this->validatePluginZip($archive);

        $archive->extractTo($destination);

        $this->clearOpcodeCache();
    }

    /**
     * Iterates all files of the provided zip archive
     * path and validates the plugin namespace, directory traversal
     * and multiple plugin directories.
     *
     * @throws Exception
     */
    private function validatePluginZip(ZipArchive $archive): void
    {
        $prefix = $this->getLegacyPluginPrefix($archive);

        $this->assertValid($archive, $prefix);
    }

    private function assertValid(ZipArchive $archive, string $prefix): void
    {
        for ($i = 2; $i < $archive->numFiles; ++$i) {
            $stat = $archive->statIndex($i);
            if (!\is_array($stat)) {
                continue;
            }

            $this->assertNoDirectoryTraversal($stat['name']);
            $this->assertPrefix($stat['name'], $prefix);
        }
    }

    private function getLegacyPluginPrefix(ZipArchive $archive): string
    {
        $segments = $archive->statIndex(0);
        if (!\is_array($segments)) {
            throw new RuntimeException('Uploaded zip archive contains no plugin namespace directory');
        }
        $segments = array_filter(explode('/', $segments['name']));

        if (!\in_array($segments[0], ['Frontend', 'Backend', 'Core'])) {
            throw new RuntimeException('Uploaded zip archive contains no plugin namespace directory');
        }

        if (\count($segments) <= 1) {
            $segmentsTmp = $archive->statIndex(1);
            if (\is_array($segmentsTmp)) {
                $segments = array_filter(explode('/', $segmentsTmp['name']));
            }
        }

        return implode('/', [$segments[0], $segments[1]]);
    }

    /**
     * Clear opcode caches to make sure that the
     * updated plugin files are used in the following requests.
     */
    private function clearOpcodeCache(): void
    {
        if (\function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (\function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }

    private function assertPrefix(string $filename, string $prefix): void
    {
        if (!str_starts_with($filename, $prefix)) {
            throw new RuntimeException(sprintf('Detected invalid file/directory %s in the plugin zip: %s', $filename, $prefix));
        }
    }

    private function assertNoDirectoryTraversal(string $filename): void
    {
        if (str_contains($filename, '../')) {
            throw new RuntimeException('Directory Traversal detected');
        }
    }
}
