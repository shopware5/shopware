<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\MediaBundle\Commands;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Exception\ORMException;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MediaCleanupCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('sw:media:cleanup')
            ->setHelp('The <info>%command.name%</info> collects unused media and moves them to the recycle bin album.')
            ->setDescription('Collect unused media move them to trash.')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete unused media.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Searching for unused media files.');
        $total = $this->handleMove();
        $io->text(sprintf('%s unused item(s) found.', $total));

        if ($total === 0) {
            return 0;
        }

        if ($input->getOption('delete')) {
            if ($input->isInteractive() && !$io->confirm('Are you sure you want to delete every item in the recycle bin?')) {
                return 0;
            }

            $deleted = $this->handleCleanup($io);
            $io->success(sprintf('%d item(s) deleted.', $deleted));

            return 0;
        }

        $io->success(sprintf('%d item(s) in recycle bin.', $total));

        return 0;
    }

    /**
     * Handles cleaning process and returns the number of deleted media objects
     */
    private function handleCleanup(SymfonyStyle $io): int
    {
        $em = $this->getContainer()->get(ModelManager::class);

        $query = $em->getRepository(Media::class)->getAlbumMediaQuery(Album::ALBUM_GARBAGE);
        $query->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);

        $count = (int) $em->getQueryCount($query);
        $iterableResult = $query->toIterable();

        $progressBar = $io->createProgressBar($count);

        try {
            foreach ($iterableResult as $key => $media) {
                $em->remove($media);
                if ($key % 100 === 0) {
                    $em->flush();
                    $em->clear();
                }
                $progressBar->advance();
            }
            $em->flush();
            $em->clear();
        } catch (ORMException $e) {
            $count = 0;
        }

        $progressBar->finish();
        $io->newLine(2);

        return $count;
    }

    private function handleMove(): int
    {
        $gc = $this->getContainer()->get('shopware_media.garbage_collector');
        $gc->run();

        return $gc->getCount();
    }
}
