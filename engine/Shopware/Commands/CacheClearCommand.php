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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheClearCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:cache:clear')
            ->setDescription('Clears the cache')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputIsVerbose = $output->isVerbose();
        $io = new SymfonyStyle($input, $output);

        $realCacheDir = $this->getContainer()->getParameter('kernel.cache_dir');
        // the old cache dir name must not be longer than the real one to avoid exceeding
        // the maximum length of a directory or file path within it (esp. Windows MAX_PATH)
        $oldCacheDir = substr($realCacheDir, 0, -1) . (substr($realCacheDir, -1) === '~' ? '+' : '~');
        $filesystem = $this->getContainer()->get('file_system');

        if (!is_writable($realCacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write into directory "%s"', $realCacheDir));
        }

        if ($filesystem->exists($oldCacheDir)) {
            $filesystem->remove($oldCacheDir);
        }

        $kernel = $this->getContainer()->get('kernel');
        $io->comment(sprintf('Clearing the cache for the <info>%s</info> environment', $kernel->getEnvironment()));

        $filesystem->rename($realCacheDir, $oldCacheDir);

        if ($outputIsVerbose) {
            $io->comment('Removing old cache directory...');
        }

        $filesystem->remove($oldCacheDir);
        if ($outputIsVerbose) {
            $io->comment('Finished');
        }

        $io->success(sprintf('Cache for the "%s" environment was successfully cleared.', $kernel->getEnvironment()));
    }
}
