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

class MediaMigration
{
    /**
     * @var MediaPathNormalizer
     */
    private $mediaPathNormalizer;

    /**
     * @var array
     */
    private $validExtensions = [
        'jpg' => 1,
        'jpeg' => 1,
        'gif' => 1,
        'png' => 1
    ];

    /**
     * @param MediaPathNormalizer $mediaPathNormalizer
     */
    public function __construct(MediaPathNormalizer $mediaPathNormalizer)
    {
        $this->mediaPathNormalizer = $mediaPathNormalizer;
    }

    /**
     * Batch migration
     *
     * @param MediaBackendInterface $fromFilesystem
     * @param MediaBackendInterface $toFileSystem
     * @throws \Exception
     */
    public function migrate(MediaBackendInterface $fromFilesystem, MediaBackendInterface $toFileSystem)
    {
        foreach ($this->iterateOldMediaFiles($fromFilesystem) as $path) {
            $this->migrateFile($path, $fromFilesystem, $toFileSystem);
        }
    }

    /**
     * Migrate a single file
     *
     * @param $path
     * @param MediaBackendInterface $fromFilesystem
     * @param MediaBackendInterface $toFileSystem
     * @throws \Exception
     */
    public function migrateFile($path, MediaBackendInterface $fromFilesystem, MediaBackendInterface $toFileSystem)
    {
        // Default legacy migration hooked into getRealPath()
        if ($fromFilesystem->getAdapterType() === 'local') {
            if (!$fromFilesystem->isRealPathFormat($path)) {
                print "Migrate: ".$path.PHP_EOL;
                $fromFilesystem->has($path);
            }
        }

        // file already exists
        if ($toFileSystem->has($path)) {
            print "SKIP: ".$path.PHP_EOL;
            return;
        }

        // migrate filesystem and name
        if ($fromFilesystem->has($path)) {
            echo "Move: ".$path.PHP_EOL;
            $success = $this->write($toFileSystem, $path, $fromFilesystem->read($path));
            if ($success) {
                $fromFilesystem->delete($path);
            }

            return;
        }

        throw new \Exception("File not found: ".$path);
    }

    /**
     * File collector with some filtering
     *  - no dot files
     *  - only media files
     *
     * @param MediaBackendInterface $fromFileSystem
     * @param string $path
     * @return \Generator
     */
    private function iterateOldMediaFiles(MediaBackendInterface $fromFileSystem, $path = 'media')
    {
        $files = [];
        foreach ($fromFileSystem->listContents($path) as $item) {
            $normalized = substr($item['path'], strpos($item['path'], 'media'));
            $isMediaFile = strstr($item['path'], 'media') !== false && strstr($normalized, '/.') === false;

            if (!$isMediaFile) {
                continue;
            }
            if ($item['type'] != 'dir') {
                if (!isset($item['extension']) || !isset($this->validExtensions[$item['extension']])) {
                    continue;
                }

                $files[] = $normalized;
            } elseif ($item['type'] == 'dir') {
                $files = array_merge($files, $this->iterateOldMediaFiles($fromFileSystem, $normalized));
            }
        }

        return $files;
    }

    /**
     * @param MediaBackendInterface $toFileSystem
     * @param string $path
     * @param string $contents
     * @return boolean
     */
    private function write($toFileSystem, $path, $contents)
    {
        $dirString = '';
        $dirs = explode('/', dirname($path));
        foreach ($dirs as $dir) {
            $dirString .= '/' . $dir;
            $toFileSystem->createDir($dirString);
        }

        $toFileSystem->write($path, $contents);

        return $toFileSystem->has($path);
    }
}
