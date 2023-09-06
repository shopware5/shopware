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
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\ShopwareReleaseStruct;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class PluginExtractor
{
    private string $pluginDir;

    private Filesystem $filesystem;

    /**
     * @var string[]
     */
    private array $pluginDirectories;

    private ShopwareReleaseStruct $release;

    private RequirementValidator $requirementsValidator;

    /**
     * @param string[] $pluginDirectories
     */
    public function __construct(
        string $pluginDir,
        Filesystem $filesystem,
        array $pluginDirectories,
        ShopwareReleaseStruct $release,
        RequirementValidator $requirementValidator
    ) {
        $this->pluginDir = $pluginDir;
        $this->filesystem = $filesystem;
        $this->pluginDirectories = $pluginDirectories;
        $this->release = $release;
        $this->requirementsValidator = $requirementValidator;
    }

    /**
     * Extracts the provided zip file to the provided destination
     *
     * @param ZipArchive $archive
     *
     * @throws Exception
     *
     * @return void
     */
    public function extract($archive)
    {
        $destination = $this->pluginDir;

        if (!is_writable($destination)) {
            throw new Exception(sprintf('Destination directory "%s" is not writable', $destination));
        }

        $prefix = $this->getPluginPrefix($archive);
        $this->validatePluginZip($prefix, $archive);
        $this->validatePluginRequirements($prefix, $archive);

        $oldFile = $this->findOldFile($prefix);
        $backupFile = $this->createBackupFile($oldFile);

        try {
            $archive->extractTo($destination);

            if ($backupFile !== false) {
                $this->filesystem->remove($backupFile);
            }

            unlink($archive->filename);
        } catch (Exception $e) {
            if ($oldFile !== false) {
                $this->filesystem->rename($backupFile, $oldFile);
            }
            throw $e;
        }

        $this->clearOpcodeCache();
    }

    /**
     * Iterates all files of the provided zip archive
     * path and validates the plugin namespace, directory traversal
     * and multiple plugin directories.
     */
    private function validatePluginZip(string $prefix, ZipArchive $archive): void
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

    private function getPluginPrefix(ZipArchive $archive): string
    {
        $entry = $archive->statIndex(0);
        if (!\is_array($entry)) {
            return '';
        }

        return explode('/', $entry['name'])[0];
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
        if (strpos($filename, $prefix) !== 0) {
            throw new RuntimeException(sprintf('Detected invalid file/directory %s in the plugin zip: %s', $filename, $prefix));
        }
    }

    private function assertNoDirectoryTraversal(string $filename): void
    {
        if (strpos($filename, '../') !== false) {
            throw new RuntimeException('Directory Traversal detected');
        }
    }

    /**
     * @return false|string
     */
    private function findOldFile(string $pluginName)
    {
        $dir = $this->pluginDir . '/' . $pluginName;
        if ($this->filesystem->exists($dir)) {
            return $dir;
        }

        foreach ($this->pluginDirectories as $directory) {
            $namespaces = ['Core', 'Frontend', 'Backend'];
            foreach ($namespaces as $namespace) {
                $dir = $directory . $namespace . '/' . $pluginName;

                if ($this->filesystem->exists($dir)) {
                    return $dir;
                }
            }
        }

        return false;
    }

    /**
     * @param string|false $oldFile
     *
     * @return false|string
     */
    private function createBackupFile($oldFile)
    {
        if ($oldFile === false) {
            return false;
        }

        $backupFile = $oldFile . '.' . uniqid();
        $this->filesystem->rename($oldFile, $backupFile);

        return $backupFile;
    }

    private function validatePluginRequirements(string $prefix, ZipArchive $archive): void
    {
        $xml = $archive->getFromName($prefix . '/plugin.xml');
        if (!$xml) {
            return;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), uniqid()) . '.xml';
        file_put_contents($tmpFile, $xml);
        try {
            $this->requirementsValidator->validate($tmpFile, $this->release->getVersion());
        } finally {
            unlink($tmpFile);
        }
    }
}
