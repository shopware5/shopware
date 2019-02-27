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

namespace Shopware\Recovery\Update\Command;

use Shopware\Components\Migrations\Manager as MigrationManager;
use Shopware\Recovery\Common\DumpIterator;
use Shopware\Recovery\Common\IOHelper;
use Shopware\Recovery\Update\Cleanup;
use Shopware\Recovery\Update\CleanupFilesFinder;
use Shopware\Recovery\Update\DependencyInjection\Container;
use Shopware\Recovery\Update\DummyPluginFinder;
use Shopware\Recovery\Update\FilesystemFactory;
use Shopware\Recovery\Update\PathBuilder;
use Shopware\Recovery\Update\Steps\ErrorResult;
use Shopware\Recovery\Update\Steps\MigrationStep;
use Shopware\Recovery\Update\Steps\SnippetStep;
use Shopware\Recovery\Update\Steps\UnpackStep;
use Shopware\Recovery\Update\Steps\ValidResult;
use Shopware\Recovery\Update\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var IOHelper
     */
    private $IOHelper;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('update');
        $this->setDescription('Updates shopware');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->getApplication()->getContainer();
        $this->container->setParameter('update.config', []);

        $this->IOHelper = $ioService = new IOHelper(
            $input,
            $output,
            $this->getHelper('question')
        );

        if (!is_dir(UPDATE_FILES_PATH) && !is_dir(UPDATE_ASSET_PATH)) {
            $ioService->writeln('No update files found.');

            return 1;
        }

        $version = $this->container->get('shopware.version');

        if ($ioService->isInteractive()) {
            $ioService->cls();
            $ioService->printBanner();
            $ioService->writeln('<info>Welcome to the Shopware updater </info>');
            $ioService->writeln(sprintf('Shopware Version %s', $version));
            $ioService->writeln('');
            $ioService->ask('Press return to start the update.');
            $ioService->cls();
        }

        $this->unpackFiles();
        $this->migrateDatabase();
        $this->importSnippets();
        $this->cleanup();
        $this->synchronizeThemes();
        $this->writeLockFile();

        $ioService->cls();
        $ioService->writeln('');
        $ioService->writeln('');
        $ioService->writeln('<info>The update has been finished succesfuly.</info>');
        $ioService->writeln('Your shop is currently in maintenance mode.');
        $ioService->writeln(sprintf('Please delete <question>%s</question> to finish the update.', UPDATE_ASSET_PATH));
        $ioService->writeln('');
    }

    private function unpackFiles()
    {
        $this->IOHelper->writeln('Replace system files...');
        if (!is_dir(UPDATE_FILES_PATH)) {
            $this->IOHelper->writeln('skipped...');

            return;
        }

        /** @var FilesystemFactory $factory */
        $factory = $this->container->get('filesystem.factory');
        $localFilesytem = $factory->createLocalFilesystem();
        $remoteFilesystem = $factory->createLocalFilesystem();

        /** @var PathBuilder $pathBuilder */
        $pathBuilder = $this->container->get('path.builder');

        $debug = false;
        $step = new UnpackStep($localFilesytem, $remoteFilesystem, $pathBuilder, $debug);

        $offset = 0;
        $total = 0;
        do {
            $result = $step->run($offset, $total);
            if ($result instanceof ErrorResult) {
                throw new \Exception($result->getMessage(), 0, $result->getException());
            }
            $offset = $result->getOffset();
            $total = $result->getTotal();
        } while ($result instanceof ValidResult);
    }

    private function migrateDatabase()
    {
        $this->IOHelper->writeln('Apply database migrations...');

        if (!is_dir(UPDATE_ASSET_PATH . '/migrations/')) {
            $this->IOHelper->writeln('skipped...');

            return 1;
        }

        /** @var MigrationManager $manager */
        $manager = $this->container->get('migration.manager');

        $currentVersion = $manager->getCurrentVersion();

        $versions = $manager->getMigrationsForVersion($currentVersion);

        $progress = $this->IOHelper->createProgressBar(count($versions));
        $progress->start();

        $step = new MigrationStep($manager);
        $offset = 0;
        do {
            $progress->setProgress($offset);
            $result = $step->run($offset);
            if ($result instanceof ErrorResult) {
                throw new \Exception($result->getMessage(), 0, $result->getException());
            }

            $offset = $result->getOffset();
            $progress->setProgress($offset);
        } while ($result instanceof ValidResult);
        $progress->finish();
        $this->IOHelper->writeln('');
    }

    private function importSnippets()
    {
        $this->IOHelper->writeln('Import snippets...');

        /** @var DumpIterator $dump */
        $dump = $this->container->get('dump');

        if (!$dump) {
            $this->IOHelper->writeln('skipped...');

            return 1;
        }

        /** @var \PDO $conn */
        $conn = $this->container->get('db');
        $snippetStep = new SnippetStep($conn, $dump);

        $progress = $this->IOHelper->createProgressBar($dump->count());
        $progress->start();

        $offset = 0;
        do {
            $progress->setProgress($offset);
            $result = $snippetStep->run($offset);
            if ($result instanceof ErrorResult) {
                throw new \Exception($result->getMessage(), 0, $result->getException());
            }
            $offset = $result->getOffset();
            $progress->setProgress($offset);
        } while ($result instanceof ValidResult);
        $progress->finish();
        $this->IOHelper->writeln('');
    }

    private function cleanup()
    {
        $this->IOHelper->writeln('Cleanup old files, clearing caches...');

        $this->deleteDummyPlugins();
        $this->cleanupFiles();
    }

    private function deleteDummyPlugins()
    {
        /** @var DummyPluginFinder $pluginFinder */
        $pluginFinder = $this->container->get('dummy.plugin.finder');
        foreach ($pluginFinder->getDummyPlugins() as $plugin) {
            Utils::cleanPath($plugin);
        }
    }

    private function cleanupFiles()
    {
        /** @var CleanupFilesFinder $cleanupFilesFinder */
        $cleanupFilesFinder = $this->container->get('cleanup.files.finder');
        foreach ($cleanupFilesFinder->getCleanupFiles() as $path) {
            Utils::cleanPath($path);
        }

        /** @var Cleanup $cleanup */
        $cleanup = $this->container->get('shopware.update.cleanup');
        $cleanup->cleanup(false);
    }

    private function writeLockFile()
    {
        if (is_dir(SW_PATH . '/recovery/install')) {
            /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
            $systemLocker = $this->container->get('system.locker');
            $systemLocker();
        }
    }

    private function synchronizeThemes()
    {
        /** @var \Shopware\Components\Theme\Installer $themeService */
        $themeService = $this->container->get('shopware.theme_installer');
        $themeService->synchronize();
    }
}
