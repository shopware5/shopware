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
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ThumbnailCleanupCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:thumbnail:cleanup')
            ->setDescription('Deletes thumbnails for images whose original file has been deleted.')
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

        $builder = $em->createQueryBuilder();
        $builder->select(array('album', 'settings', 'media'))
            ->from('Shopware\Models\Media\Album', 'album')
            ->leftJoin('album.settings', 'settings')
            ->leftJoin('album.media', 'media');

        if (!empty($albumId)) {
            $builder->where('album.id = :albumId')->setParameter('albumId', $albumId);
        }

        $albumArray = $builder->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($albumArray as $album) {
            $output->writeln("Deleting unused Thumbnails for album {$album['name']} (ID: {$album['id']})");

            $sizes = $album['settings']['thumbnailSize'];

            if (empty($sizes)) {
                continue;
            }

            foreach ($album['media'] as $media) {
                $path = Shopware()->DocPath() . $media['path'];
                if ($mediaService->has($path)) {
                    continue;
                }

                $paths = $this->getMediaThumbnailPaths($media, explode(';', $sizes));

                foreach ($paths as $path) {
                    if ($mediaService->has($path)) {
                        $mediaService->delete($path);
                        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                            $output->writeln("Deleting {$path}");
                        }
                    }
                }
            }
        }

        $output->writeln("Cleanup was finished successfully");
    }

    /**
     * Returns all thumbnails paths according to the given media object
     *
     * @param $media
     * @param $sizes
     * @return array
     */
    private function getMediaThumbnailPaths($media, $sizes)
    {
        $sizes = array_merge($sizes, array('140x140'));
        $sizes = array_unique($sizes);

        $thumbnails = array();

        //iterate thumbnail sizes
        foreach ($sizes as $size) {
            if (strpos($size, 'x') === false) {
                $size = $size . 'x' . $size;
            }

            $thumbnailDir = Shopware()->DocPath('media_' . strtolower($media['type'])) . 'thumbnail' . DIRECTORY_SEPARATOR;
            $path = $thumbnailDir . $this->removeSpecialCharacters($media['name']) . '_' . $size;
            if (DIRECTORY_SEPARATOR !== '/') {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }

            $thumbnails[] = $path . '.jpg';

            if ($media['extension'] !== 'jpg') {
                $thumbnails[] = $path . '.' . $media['extension'];
            }
        }

        return $thumbnails;
    }

    /**
     * Removes special characters from a filename
     *
     * @param $name
     * @return string
     */
    private function removeSpecialCharacters($name)
    {
        $name = iconv('utf-8', 'ascii//translit', $name);
        $name = preg_replace('#[^A-z0-9\-_]#', '-', $name);
        $name = preg_replace('#-{2,}#', '-', $name);
        $name = trim($name, '-');
        return mb_substr($name, 0, 180);
    }
}
