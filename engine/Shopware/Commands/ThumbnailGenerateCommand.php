<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Models\Media\Media;
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
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class ThumbnailGenerateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:thumbnail:generate')
                ->setDescription('Generates a new Thumbnail.')
                ->addOption(
                    'albumid',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'ID of the album which contains the images'
                )->addOption(
                    'force',
                    'f',
                    InputOption::VALUE_NONE,
                    'Force complete thumbnail generation'
                )->setHelp(
                    <<<EOF
                    The <info>%command.name%</info> generates a thumbnail.
EOF
                );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $albumId = (int)$input->getOption('albumid');
        $force = (bool)$input->getOption('force');

        $em = $this->getContainer()->get('models');

        $builder = $em->createQueryBuilder();
        $builder->select(array('album', 'settings', 'media'))
                ->from('Shopware\Models\Media\Album', 'album')
                ->leftJoin('album.settings', 'settings')
                ->leftJoin('album.media', 'media');

        if (!empty($albumId)) {
            $builder->where('album.id = :albumId')->setParameter('albumId', $albumId);
        }

        $albumArray = $builder->getQuery()->getResult();
        $generator = $this->getContainer()->get('thumbnail_manager');

        $progress = $this->getHelperSet()->get('progress');

        $errors = array();

        foreach ($albumArray as $album) {
            $sizes = $album->getSettings()->getThumbnailSize();

            //no size configured or no media object? continue
            if (empty($sizes) || empty($sizes[0]) || $album->getMedia()->count() === 0) {
                continue;
            }

            $output->writeln("Generating Thumbnails for Album {$album->getName()} (ID: {$album->getId()})");

            $progress->start($output, $album->getMedia()->count());

            /**@var $media Media */
            foreach ($album->getMedia() as $media) {
                if (!file_exists(Shopware()->OldPath() . DIRECTORY_SEPARATOR . $media->getPath())) {
                    $errors[] = 'Base image file does not exist: ' . $media->getPath();
                    $progress->advance();
                    continue;
                }

                $thumbnails = $media->getThumbnailFilePaths();

                //check each single thumbnail to skip already existing thumbnails
                foreach ($thumbnails as $size => $path) {
                    $tmp = Shopware()->OldPath() . $path;
                    if (file_exists($tmp) && !($force)) {
                        continue;
                    }

                    $generator->createMediaThumbnail($media, array($size));
                }

                $progress->advance();
            }

            $progress->finish();
        }

        if (!empty($errors)) {
            $output->writeln('Thumbnail generation finished with errors:');

            foreach ($errors as $error) {
                $output->writeln($error);
            }
        } else {
            $output->writeln('Thumbnail generation finished successfully');
        }
    }
}
