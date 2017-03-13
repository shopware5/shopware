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

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shopware ThumbnailGenerateCommand Class
 *
 * This class is used as a command to generate thumbnails from media albums.
 * If no album is defined, thumbnails from all album medias are created.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThumbnailGenerateCommand extends ShopwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Manager
     */
    private $generator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:thumbnail:generate')
            ->setDescription('Generates a new Thumbnail.')
            ->addOption(
                'albumid',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of the album which contains the images'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force complete thumbnail generation'
            )
            ->setHelp('The <info>%command.name%</info> generates a thumbnail.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->force = (bool) $input->getOption('force');
        $this->errors = [];
        $this->generator = $this->getContainer()->get('thumbnail_manager');

        $albumId = (int) $input->getOption('albumid');

        foreach ($this->getMediaAlbums($albumId) as $album) {
            $this->createAlbumThumbnails($album);
        }

        $this->printExitMessage();
    }

    /**
     * @param int $albumId
     *
     * @return Album[]
     */
    protected function getMediaAlbums($albumId)
    {
        /** @var ModelManager $em */
        $em = $this->getContainer()->get('models');

        $builder = $em->createQueryBuilder();
        $builder
            ->select(['album', 'settings'])
            ->from('Shopware\Models\Media\Album', 'album')
            ->innerJoin('album.settings', 'settings', 'WITH', 'settings.createThumbnails = 1');

        if (!empty($albumId)) {
            $builder
                ->where('album.id = :albumId')
                ->setParameter('albumId', $albumId);
        }

        return $builder->getQuery()->getResult();
    }

    protected function printExitMessage()
    {
        if (0 === count($this->errors)) {
            $this->output->writeln('<info>Thumbnail generation finished successfully</info>');

            return;
        }

        $this->output->writeln('<error>Thumbnail generation finished with errors</error>');

        foreach ($this->errors as $error) {
            $this->output->writeln('<comment>' . $error . '</comment>');
        }
    }

    /**
     * @param Album $album
     *
     * @throws Exception
     */
    private function createAlbumThumbnails(Album $album)
    {
        $this->output->writeln("Generating Thumbnails for Album {$album->getName()} (ID: {$album->getId()})");

        /**
         * @var ModelManager
         */
        $em = $this->getContainer()->get('models');
        /**
         * @var Repository
         */
        $repository = $em->getRepository(Media::class);

        $query = $repository->getAlbumMediaQuery($album->getId());
        $paginator = $em->createPaginator($query);

        $total = $paginator->count();

        $progressBar = new ProgressBar($this->output, $total);
        $progressBar->start();

        /*
         * @var Media
         */
        foreach ($paginator->getIterator() as $media) {
            try {
                $this->createMediaThumbnails($media);
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        // force newline when processing the next album
        $this->output->writeln('');
    }

    /**
     * Check each single thumbnail to skip already existing thumbnails
     *
     * @param Media $media
     *
     * @throws Exception
     */
    private function createMediaThumbnails(Media $media)
    {
        if (!$this->imageExists($media)) {
            throw new Exception('Base image file does not exist: ' . $media->getPath());
        }

        $thumbnails = $media->getThumbnailFilePaths();
        foreach ($thumbnails as $size => $path) {
            if (!$this->force && $this->thumbnailExists($path)) {
                continue;
            }

            $this->generator->createMediaThumbnail($media, [$size], true);
        }
    }

    /**
     * @param string $thumbnailPath
     *
     * @throws Exception
     *
     * @return bool
     */
    private function thumbnailExists($thumbnailPath)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        return $mediaService->getFilesystem()->has($thumbnailPath);
    }

    /**
     * @param Media $media
     *
     * @throws Exception
     *
     * @return bool
     */
    private function imageExists(Media $media)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        return $mediaService->getFilesystem()->has($media->getPath());
    }
}
