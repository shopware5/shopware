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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class MediaMigration
{
    /**
     * @var array
     */
    private $counter = [
        'migrated' => 0,
        'skipped' => 0,
        'moved' => 0,
    ];

    /**
     * Batch migration
     *
     * @param bool $skipScan
     */
    public function migrate(MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem, OutputInterface $output, $skipScan = false)
    {
        $output->writeln(' // Migrating all media files in your filesystem. This might take some time, depending on the number of media files you have.');
        $output->writeln('');

        $filesToMigrate = 0;

        if (!$skipScan) {
            $filesToMigrate = $this->countFilesToMigrate('media', $fromFilesystem);
        }

        $progressBar = new ProgressBar($output, $filesToMigrate);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent%%,  %migrated% migrated, %skipped% skipped, %moved% moved, Elapsed: %elapsed%' . "\n" . ' Current file: %filename%');
        $progressBar->setMessage('', 'filename');
        $this->migrateFilesIn('media', $fromFilesystem, $toFileSystem, $progressBar);
        $progressBar->finish();

        $rows = [];
        foreach ($this->counter as $key => $value) {
            $rows[] = [$key, $value];
        }

        $output->writeln('');
        $output->writeln('');

        $table = new Table($output);
        $table->setStyle('borderless');
        $table->setHeaders(['Action', 'Number of items']);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Migrate a single file
     *
     * @param string $path
     *
     * @throws \RuntimeException
     */
    private function migrateFile($path, MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFileSystem)
    {
        // only do migration if it's on the local filesystem since could take a long time
        // to read and write all the files
        if ($fromFilesystem->getAdapterType() === 'local') {
            if (!$fromFilesystem->isEncoded($path)) {
                ++$this->counter['migrated'];
                $fromFilesystem->migrateFile($path);
            }
        }

        // file already exists
        if ($toFileSystem->has($path)) {
            ++$this->counter['skipped'];

            return;
        }

        // move file to new filesystem and remove the old one
        if ($fromFilesystem->has($path)) {
            ++$this->counter['moved'];
            $success = $this->writeStream($toFileSystem, $path, $fromFilesystem->readStream($path));
            if ($success) {
                $fromFilesystem->delete($path);
            }

            return;
        }

        throw new \RuntimeException('File not found: ' . $path);
    }

    /**
     * @param string   $path
     * @param resource $contents
     *
     * @return bool
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

    /**
     * @param string $directory
     */
    private function migrateFilesIn($directory, MediaServiceInterface $fromFilesystem, MediaServiceInterface $toFilesystem, ProgressBar $progressBar)
    {
        /** @var array $contents */
        $contents = $fromFilesystem->getFilesystem()->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->migrateFilesIn($item['path'], $fromFilesystem, $toFilesystem, $progressBar);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                $progressBar->setMessage($item['path'], 'filename');

                $this->migrateFile($item['path'], $fromFilesystem, $toFilesystem);

                foreach ($this->counter as $key => $value) {
                    $progressBar->setMessage($value, $key);
                }

                $progressBar->advance();
            }
        }
    }

    /**
     * @param string $directory
     *
     * @return int
     */
    private function countFilesToMigrate($directory, MediaServiceInterface $filesystem)
    {
        /** @var array $contents */
        $contents = $filesystem->getFilesystem()->listContents($directory);
        $cnt = 0;

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $cnt += $this->countFilesToMigrate($item['path'], $filesystem);
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                ++$cnt;
            }
        }

        return $cnt;
    }
}
