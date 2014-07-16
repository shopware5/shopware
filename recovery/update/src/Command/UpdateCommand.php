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

namespace Shopware\Recovery\Update\Command;

use Shopware\Components\Migrations\Manager as MigrationManager;
use Shopware\Recovery\Common\Dump;
use Shopware\Recovery\Update\DependencyInjection\Container;
use Shopware\Recovery\Update\FilesystemFactory;
use Shopware\Recovery\Update\PathBuilder;
use Shopware\Recovery\Update\Steps\ErrorResult;
use Shopware\Recovery\Update\Steps\MigrationStep;
use Shopware\Recovery\Update\Steps\SnippetStep;
use Shopware\Recovery\Update\Steps\UnpackStep;
use Shopware\Recovery\Update\Steps\ValidResult;
use Shopware\Recovery\Update\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Container
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('update');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input     = $input;
        $this->output    = $output;
        $this->container = $this->getApplication()->getContainer();

        if (!is_dir(UPDATE_FILES_PATH) && !is_dir(UPDATE_ASSET_PATH)) {
            $this->output->writeln("No update files found.");
            return 1;
        }

        $this->container->setParameter('update.config', array());

        $this->unpackFiles();
        $this->migrateDatabase();
        $this->importSnippets();
        $this->cleanup();
    }

    private function cleanup()
    {
        $this->output->writeln("Cleanup old files, clearing caches...");

        $cleanupFile = UPDATE_ASSET_PATH . '/cleanup.php';
        if (!is_file($cleanupFile)) {
            return;
        }

        $rawList = require $cleanupFile;
        $cleanupList = array();

        foreach ($rawList as $path) {
            $realpath = SW_PATH.'/'.$path;
            if (file_exists($realpath)) {
                $cleanupList[] = $path;
            }
        }

        foreach ($cleanupList as $path) {
            Utils::cleanPath(SW_PATH.'/'.$path);
        }

        $directoriesToDelete = array(
            'cache/proxies/'                   => false,
            'cache/doctrine/filecache/'	       => false,
            'cache/doctrine/proxies/'	       => false,
            'cache/doctrine/attributes/'       => false,
            'cache/general/'                   => false,
            'cache/templates/'                 => false,
            'engine/Library/Mpdf/tmp'          => false,
            'engine/Library/Mpdf/ttfontdata'   => false,
        );

        foreach ($directoriesToDelete as $directory => $deleteDirecory) {
            $filePath = SW_PATH . '/' . $directory;
            Utils::deleteDir($filePath, $deleteDirecory);
        }
    }

    private function migrateDatabase()
    {
        $this->output->writeln("Apply database migrations...");

        if (!is_dir(UPDATE_ASSET_PATH . '/migrations/')) {
            $this->output->writeln("skipped...");
            return 1;
        }

        /** @var MigrationManager $migrationManager */
        $manager = $this->container->get('migration.manager');

        $currentVersion = $manager->getCurrentVersion();

        $versions = $manager->getMigrationsForVersion($currentVersion);

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($this->output, count($versions));

        $step = new MigrationStep($manager);
        $offset = 0;
        do {
            $progress->setCurrent($offset);
            $result = $step->run($offset);
            if ($result instanceof ErrorResult) {
                throw new \Exception($result->getMessage(), 0, $result->getException());
            }

            $offset = $result->getOffset();
            $progress->setCurrent($offset);
        } while ($result instanceof ValidResult);
        $progress->finish();
    }

    public function importSnippets()
    {
        $this->output->writeln("Import snippets...");

        /** @var Dump $dump */
        $dump = $this->container->get('dump');

        if (!$dump) {
            $this->output->writeln("skipped...");
            return 1;
        }

        /** @var \PDO $conn */
        $conn = $this->container->get('db');

        $this->output->writeln("Importing snippets");
        $snippetStep = new SnippetStep($conn, $dump);

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($this->output, $dump->count());

        $offset = 0;
        do {
            $progress->setCurrent($offset);
            $result = $snippetStep->run($offset);
            if ($result instanceof ErrorResult) {
                throw new \Exception($result->getMessage(), 0, $result->getException());
            }
            $offset = $result->getOffset();
            $progress->setCurrent($offset);
        } while ($result instanceof ValidResult);
        $progress->finish();
    }

    public function unpackFiles()
    {
        $this->output->writeln("Replace system files...");
        if (!is_dir(UPDATE_FILES_PATH)) {
            $this->output->writeln("skipped...");
            return;
        }

        /** @var FilesystemFactory $factory */
        $factory = $this->container->get('filesystem.factory');
        $localFilesytem   = $factory->createLocalFilesystem();
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
            $total  = $result->getTotal();
        } while ($result instanceof ValidResult);
    }
}
