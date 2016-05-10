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

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
class PluginExtractor
{
    /**
     * Extracts the provided zip file to the provided destination
     *
     * @param string $file
     * @param string $destination
     * @throws \Exception
     */
    public function extract($file, $destination)
    {
        $stream = $this->validatePluginZip($file);

        if (!is_writable($destination)) {
            throw new \Exception(
                'Destination directory is not writable'
            );
        }

        $stream->extractTo($destination);

        $this->clearOpcodeCache();
    }

    /**
     * Iterates all files of the provided zip archive
     * path and validates the plugin namespace, directory traversal
     * and multiple plugin directories.
     *
     * @param string $filePath
     * @return \ZipArchive
     */
    private function validatePluginZip($filePath)
    {
        $stream = $this->openZip($filePath);

        $namespace = $this->getPluginNamespace($stream);

        for ($i = 2; $i < $stream->numFiles; $i++) {
            $stat = $stream->statIndex($i);

            if (strpos($stat['name'], '../') !== false) {
                throw new \RuntimeException(
                    sprintf('Directory Traversal detected')
                );
            }

            if (strpos($stat['name'], $namespace) !== 0) {
                throw new \RuntimeException(
                    sprintf(
                        'Detected invalid file/directory %s in the plugin zip: %s',
                        $stat['name'],
                        $namespace
                    )
                );
            }
        }

        return $stream;
    }

    /**
     * @param \ZipArchive $stream
     * @return string
     */
    private function getPluginNamespace(\ZipArchive $stream)
    {
        $segments = $stream->statIndex(0);
        $segments = array_filter(explode('/', $segments['name']));

        if (count($segments) <= 1) {
            $segments = $stream->statIndex(1);
            $segments = array_filter(explode('/', $segments['name']));
        }

        if (!in_array($segments[0], ['Frontend', 'Backend', 'Core'])) {
            throw new \RuntimeException(
                sprintf('Uploaded zip archive contains no plugin namespace directory: %s', $segments[1])
            );
        }

        return implode('/', $segments);
    }

    /**
     * @param $file
     * @return \ZipArchive
     */
    private function openZip($file)
    {
        $stream = new \ZipArchive();

        if (true !== ($retVal = $stream->open($file, null))) {
            throw new \RuntimeException(
                $this->getErrorMessage($retVal, $file),
                $retVal
            );
        }

        return $stream;
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
     * @param $retval
     * @param $file
     * @return string
     */
    protected function getErrorMessage($retval, $file)
    {
        switch ($retval) {
            case \ZipArchive::ER_EXISTS:
                return sprintf("File '%s' already exists.", $file);
            case \ZipArchive::ER_INCONS:
                return sprintf("Zip archive '%s' is inconsistent.", $file);
            case \ZipArchive::ER_INVAL:
                return sprintf("Invalid argument (%s)", $file);
            case \ZipArchive::ER_MEMORY:
                return sprintf("Malloc failure (%s)", $file);
            case \ZipArchive::ER_NOENT:
                return sprintf("No such zip file: '%s'", $file);
            case \ZipArchive::ER_NOZIP:
                return sprintf("'%s' is not a zip archive.", $file);
            case \ZipArchive::ER_OPEN:
                return sprintf("Can't open zip file: %s", $file);
            case \ZipArchive::ER_READ:
                return sprintf("Zip read error (%s)", $file);
            case \ZipArchive::ER_SEEK:
                return sprintf("Zip seek error (%s)", $file);
            default:
                return sprintf("'%s' is not a valid zip archive, got error code: %s", $file, $retval);
        }
    }
}
