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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputArgument;
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
 * @package   Shopware\Components\Console\Command
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
    private $errors = array();

    /**
     * @var \Shopware\Components\Thumbnail\Manager
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
            ->setHelp(
                <<<EOF
The <info>%command.name%</info> generates a thumbnail.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output         = $output;
        $this->force          = (bool)$input->getOption('force');
        $this->errors         = array();
        $this->generator      = $this->getContainer()->get('thumbnail_manager');

        $albumId             = (int)$input->getOption('albumid');

        foreach ($this->getMediaAlbums($albumId) as $album) {
            $this->createAlbumThumbnails($album);
        }

        $this->printExitMessage();
    }

    /**
     * @param Album $album
     * @throws \Exception
     */
    private function createAlbumThumbnails(Album $album)
    {
        $this->output->writeln("Generating Thumbnails for Album {$album->getName()} (ID: {$album->getId()})");

        /**
         * @var ProgressHelper $progress
         */
        $progress = $this->getHelperSet()->get('progress');

        /**
         * @var ModelManager $em
         */
        $em = $this->getContainer()->get('models');
        /**
         * @var Repository $repository
         */
        $repository = $em->getRepository(Media::class);

        $offset = 0;
        $limit = 50;
        $count = 0;

        do {
            $query = $repository->getAlbumMediaQuery($album->getId(), null, null, $offset, $limit);

            $paginator = $em->createPaginator($query);

            if ($count === 0) {
                $total = $paginator->count();

                $progress->start($this->output, $total);
            }

            /**
             * @var $media Media
             */
            foreach ($paginator->getIterator() as $media) {
                $count++;

                if (!$this->imageExists($media)) {
                    $this->errors[] = 'Base image file does not exist: ' . $media->getPath();
                    $progress->advance();

                    continue;
                }

                try {
                    $this->createMediaThumbnails($media);
                } catch (\Exception $e) {
                    $this->errors[] = $e->getMessage();
                }

                $progress->advance();
            }

            $offset += $limit;
        } while ($count < $total);

        $progress->finish();
        $this->output->writeln("");
    }

    /**
     * Check each single thumbnail to skip already existing thumbnails
     *
     * @param Media $media
     * @throws \Exception
     */
    private function createMediaThumbnails(Media $media)
    {
        $thumbnails = $media->getThumbnailFilePaths();
        foreach ($thumbnails as $size => $path) {
            if ($this->thumbnailExists($path) && !($this->force)) {
                continue;
            }
            $this->generator->createMediaThumbnail($media, array($size), true);
        }
    }

    /**
     * @param string $thumbnailPath
     * @return bool
     */
    private function thumbnailExists($thumbnailPath)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        return $mediaService->has(Shopware()->DocPath() . $thumbnailPath);
    }

    /**
     * @param Media $media
     * @return bool
     */
    private function imageExists(Media $media)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        return $mediaService->has(Shopware()->DocPath() . DIRECTORY_SEPARATOR . $media->getPath());
    }

    /**
     * @param int $albumId
     * @return Album[]
     */
    protected function getMediaAlbums($albumId)
    {
        /** @var ModelManager $em */
        $em = $this->getContainer()->get('models');

        $builder = $em->createQueryBuilder();
        $builder
            ->select(array('album', 'settings'))
            ->from('Shopware\Models\Media\Album', 'album')
            ->innerJoin('album.settings', 'settings', 'WITH', 'settings.createThumbnails = 1');

        if (!empty($albumId)) {
            $builder
                ->where('album.id = :albumId')
                ->setParameter('albumId', $albumId);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * @param Album $album
     * @return bool
     */
    private function hasNoThumbnails($album)
    {
        $sizes = $album->getSettings()->getThumbnailSize();

        return empty($sizes) || empty($sizes[0]) || $album->getMedia()->count() === 0;
    }

    protected function printExitMessage()
    {
        if (empty($this->errors)) {
            $this->output->writeln('<info>Thumbnail generation finished successfully</info>');

            return;
        }

        $this->output->writeln('<error>Thumbnail generation finished with errors</error>');
        foreach ($this->errors as $error) {
            $this->output->writeln("<comment>" . $error . "</comment>");
        }
    }
}
