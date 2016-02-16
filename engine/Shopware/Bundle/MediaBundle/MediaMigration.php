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

use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MediaMigration
 * @package Shopware\Bundle\MediaBundle
 */
class MediaMigration
{
    /**
     * @var array
     */
    private $counter = [
        'migrated' => 0,
        'skipped' => 0,
        'moved' => 0
    ];

    /**
     * Batch migration
     *
     * @param MediaServiceInterface $fromFilesystem
     * @param MediaServiceInterface $toFileSystem
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function migrate(MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem, OutputInterface $output)
    {
        $output->writeln("Searching for all media files in your filesystem. This might take some time, depending on the number of media files you have.");

        foreach ($fromFilesystem->listFiles('media') as $path) {
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
     * @param string $path
     * @param MediaServiceInterface $fromFilesystem
     * @param MediaServiceInterface $toFileSystem
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function migrateFile($path, MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem, OutputInterface $output)
    {
        // only do migration if it's on the local filesystem since could take a long time
        // to read and write all the files
        if ($fromFilesystem->getAdapterType() === 'local') {
            if (!$fromFilesystem->isEncoded($path)) {
                $this->counter['migrated']++;
                $output->writeln("Migrate: ".$path);
                $fromFilesystem->migrateFile($path);
            }
        }

        // file already exists
        if ($toFileSystem->has($path)) {
            $this->counter['skipped']++;
            $output->writeln("SKIP: ".$path);
            return;
        }

        // move file to new filesystem and remove the old one
        if ($fromFilesystem->has($path)) {
            $this->counter['moved']++;
            $output->writeln("Move: ".$path);
            $success = $this->writeStream($toFileSystem, $path, $fromFilesystem->readStream($path));
            if ($success) {
                $fromFilesystem->delete($path);
            }

            return;
        }

        throw new \Exception("File not found: ".$path);
    }

    /**
     * @param MediaServiceInterface $toFileSystem
     * @param string $path
     * @param resource $contents
     * @return boolean
     */
    private function writeStream(MediaServiceInterface $toFileSystem, $path, $contents)
    {
        $path = $toFileSystem->encode($path);

        $dirString = '';
        $dirs = explode('/', dirname($path));
        foreach ($dirs as $dir) {
            $dirString .= '/' . $dir;
            $toFileSystem->createDir($dirString);
        }

        $toFileSystem->writeStream($path, $contents);

        return $toFileSystem->has($path);
    }
}
