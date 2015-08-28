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

use Symfony\Component\Console\Output\OutputInterface;

class MediaMigration
{
    /**
     * @var MediaPathNormalizer
     */
    private $mediaPathNormalizer;

    /**
     * @var array
     */
    private $counter = [
        'migrated' => 0,
        'skipped' => 0,
        'moved' => 0
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
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function migrate(MediaBackendInterface $fromFilesystem, MediaBackendInterface $toFileSystem, OutputInterface $output)
    {
        foreach ($this->iterateOldMediaFiles($fromFilesystem) as $path) {
            $this->migrateFile($path, $fromFilesystem, $toFileSystem, $output);
        }

        $status = join('. ', array_map(
            function ($v, $k) {
                return $v." ".$k;
            }, 
            $this->counter,
            array_keys($this->counter)
        ));

        $output->writeln("Job done. ".$status);
    }

    /**
     * Migrate a single file
     *
     * @param $path
     * @param MediaBackendInterface $fromFilesystem
     * @param MediaBackendInterface $toFileSystem
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function migrateFile($path, MediaBackendInterface $fromFilesystem, MediaBackendInterface $toFileSystem, OutputInterface $output)
    {
        // Default legacy migration hooked into getRealPath()
        if ($fromFilesystem->getAdapterType() === 'local') {
            if (!$fromFilesystem->isRealPathFormat($path)) {
                $this->counter['migrated']++;
                $output->writeln("Migrate: ".$path);
                $fromFilesystem->has($path);
            }
        }

        // file already exists
        if ($toFileSystem->has($path)) {
            $this->counter['skipped']++;
            $output->writeln("SKIP: ".$path);
            return;
        }

        // migrate filesystem and name
        if ($fromFilesystem->has($path)) {
            $this->counter['moved']++;
            $output->writeln("Move: ".$path);
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
