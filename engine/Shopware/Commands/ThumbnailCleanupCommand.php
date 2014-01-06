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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shopware ThumbnailCleanupCommand Class
 *
 * This class is used as a command to delete thumbnails from defined
 * media albums. If no album is defined, all album thumbnails will be removed.
 *
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class ThumbnailCleanupCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:thumbnail:cleanup')
                ->setDescription('Deletes all Album thumbnails.')
                ->addOption(
                    'albumid',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'ID of the album which contains the images'
                )->setHelp(
                    <<<EOF
                    The <info>%command.name%</info> deletes unused thumbnails.
EOF
                );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $albumId = (int)$input->getOption('albumid');

        $em = $this->getContainer()->get('models');
        $manager = $this->getContainer()->get('thumbnail_manager');

        $builder = $em->createQueryBuilder();
        $builder->select(array('album', 'settings', 'media'))
                ->from('Shopware\Models\Media\Album', 'album')
                ->leftJoin('album.settings', 'settings')
                ->leftJoin('album.media', 'media');

        if (!empty($albumId)) {
            $builder->where('album.id = :albumId')->setParameter('albumId', $albumId);
        }

        $albumArray = $builder->getQuery()->getResult();

        foreach ($albumArray as $album) {
            $output->writeln("Deleting Thumbnails for Album {$album->getName()} (ID: {$album->getId()})");

            foreach ($album->getMedia() as $media) {
                $manager->removeMediaThumbnails($media);
            }
        }

        $output->writeln("Cleanup was finished successfully");
    }
}
