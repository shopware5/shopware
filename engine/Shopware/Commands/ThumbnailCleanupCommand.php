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

namespace Shopware\Commands;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Shopware ThumbnailCleanupCommand Class
 *
 * This class is used as a command to delete thumbnails from defined
 * media albums. If no album is defined, all album thumbnails will be removed.
 *
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThumbnailCleanupCommand extends ShopwareCommand
{
    /**
     * @var array
     */
    private $baseFiles = [];

    /**
     * @var array
     */
    private $thumbnailFiles = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:thumbnail:cleanup')
            ->setDescription('Deletes thumbnails for images whose original file has been deleted.')
            ->setHelp('The <info>%command.name%</info> deletes unused thumbnails.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->removeThumbnails($io);
    }

    /**
     * @param SymfonyStyle $io
     */
    private function removeThumbnails(SymfonyStyle $io)
    {
        $filesystem = $this->getContainer()->get('shopware_media.media_service');

        $thumbnailFiles = $this->searchThumbnails($io, $filesystem);

        if (count($thumbnailFiles) === 0) {
            return;
        }

        // verbose information
        if ($io->getVerbosity() === SymfonyStyle::VERBOSITY_VERBOSE) {
            $io->caution('The following files will be deleted:');
            $io->listing($thumbnailFiles);
        }

        if (!$io->confirm(sprintf('Found %d orphaned thumbnails. Are you sure you want to delete the files? This step is irreversible.', count($thumbnailFiles)))) {
            return;
        }

        $deletedThumbnails = $this->deleteThumbnails($io, $filesystem, $thumbnailFiles);

        $io->success(sprintf('Removed %d/%d orphaned thumbnails.', $deletedThumbnails, count($thumbnailFiles)));
    }

    /**
     * @param string $directory
     * @param MediaServiceInterface $filesystem
     * @param ProgressBar $progressBar
     */
    private function processFilesIn($directory, MediaServiceInterface $filesystem, ProgressBar $progressBar)
    {
        /** @var array $contents */
        $contents = $filesystem->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->processFilesIn($item['path'], $filesystem, $progressBar);
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                $file = $filesystem->normalize($item['path']);
                $this->indexFile($file);
                $progressBar->advance();
            }
        }
    }

    /**
     * @param string $file
     */
    private function indexFile($file)
    {
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $fileName = pathinfo($file, PATHINFO_BASENAME);
        $path = str_replace($fileName, '', $file);

        // check if the filename matches thumbnail syntax like "*_200x200" or "*_200x200@2x"
        if (preg_match("/(_[0-9]+x[0-9]+(@2x)?)$/", $baseName)) {

            // strip thumbnail info to get the base filename
            $strippedName = preg_replace("/(_[0-9]+x[0-9]+(@2x)?)$/", '', $baseName);

            if (array_key_exists($strippedName, $this->baseFiles)) {
                return;
            }

            $this->thumbnailFiles[$strippedName][] = $path . 'thumbnail/' . $fileName;
            return;
        }

        $this->baseFiles[$baseName] = 1;

        if (array_key_exists($baseName, $this->thumbnailFiles)) {
            unset($this->thumbnailFiles[$baseName]);
        }
    }

    /**
     * @param SymfonyStyle $io
     * @param MediaServiceInterface $filesystem
     * @return array
     */
    private function searchThumbnails(SymfonyStyle $io, MediaServiceInterface $filesystem)
    {
        // reset internal index
        $this->baseFiles = [];
        $this->thumbnailFiles = [];
        $thumbnailFiles = [];

        $io->comment('Searching for all media files in your filesystem. This might take some time, depending on the number of media files you have.');

        $progressBar = $io->createProgressBar();
        $progressBar->setFormat('verbose');
        $this->processFilesIn('media', $filesystem, $progressBar);
        $progressBar->finish();

        if (!empty($this->thumbnailFiles)) {
            $thumbnailFiles = array_merge(...array_values($this->thumbnailFiles));
        }

        $io->newLine(2);

        return $thumbnailFiles;
    }

    /**
     * @param SymfonyStyle $io
     * @param MediaServiceInterface $filesystem
     * @param array $thumbnailFiles
     * @return int
     */
    private function deleteThumbnails(SymfonyStyle $io, MediaServiceInterface $filesystem, array $thumbnailFiles)
    {
        $deleted = 0;
        $progressBar = $io->createProgressBar(count($thumbnailFiles));
        $progressBar->setFormat('verbose');

        foreach ($thumbnailFiles as $mediaPath) {
            if ($filesystem->has($mediaPath)) {
                $filesystem->delete($mediaPath);
                $deleted++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->newLine(2);

        return $deleted;
    }
}
