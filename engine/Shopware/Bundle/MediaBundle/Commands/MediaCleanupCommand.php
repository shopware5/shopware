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

namespace Shopware\Bundle\MediaBundle\Commands;

use Doctrine\ORM\ORMException;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Commands
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaCleanupCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:media:cleanup')
            ->setHelp("The <info>%command.name%</info> collects unused media and deletes them.")
            ->setDescription("Collect unused media move them to trash.")
            ->addOption('delete', false, InputOption::VALUE_NONE, "Delete unused media.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verb = "Moved";
        $total = $this->handleMove();

        if ($input->getOption('delete')) {
            if ($input->isInteractive()) {
                $dialog = $this->getHelper('dialog');
                if (!$dialog->askConfirmation($output, 'Are you sure you want to delete every file in the recycle bin? [y/N] ', false)) {
                    return;
                }
            }

            $verb = "Deleted";
            $total = $this->handleCleanup();
        }

        $output->writeln("Cleanup: ".$verb." $total items.");
    }


    /**
     * Handles cleaning process and returns the number of deleted media objects
     *
     * @return int
     */
    private function handleCleanup()
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->getContainer()->get('models');

        $album = $em->find('Shopware\Models\Media\Album', -13);
        $mediaList = $album->getMedia();
        $total = count($mediaList);

        try {
            foreach ($mediaList as $media) {
                $em->remove($media);
            }
            $em->flush();
        } catch (ORMException $e) {
            $total = 0;
        }

        return $total;
    }

    /**
     * @return int
     */
    private function handleMove()
    {
        $gc = $this->getContainer()->get('shopware_media.garbage_collector');
        $gc->run();

        $total = (int)$gc->getCount();

        return $total;
    }
}
