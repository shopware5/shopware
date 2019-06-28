<?php
declare(strict_types=1);

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
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateMigrationCommand extends ShopwareCommand
{
    public function configure(): void
    {
        $this
            ->addOption('plugin', 'p', InputOption::VALUE_OPTIONAL, 'Plugin Name')
            ->addArgument('migrationName', InputArgument::REQUIRED, 'Migration Name')
            ->setDescription('Generates a migration file for the core or for a specific plugin');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $input->getOption('plugin');
        $migrationName = $input->getArgument('migrationName');
        $migrationDirectory = $this->findMigrationDirectory($pluginName);

        if ($migrationDirectory === null) {
            throw new \RuntimeException(sprintf('Plugin by name "%s" does not exists', $pluginName));
        }

        if (!file_exists($migrationDirectory) && !mkdir($migrationDirectory, 0777, true) && !is_dir($migrationDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $migrationDirectory));
        }

        $nextVersion = $this->getLatestMigrationVersion($migrationDirectory) + 1;

        $fileName = $this->createMigration($migrationDirectory, $migrationName, $pluginName, $nextVersion);

        $io = new SymfonyStyle($input, $output);

        $io->success(sprintf('Generated file "%s/%s"', $migrationDirectory, $fileName));
    }

    private function findMigrationDirectory(?string $pluginName): ?string
    {
        if ($pluginName === null) {
            return $this->container->getParameter('kernel.root_dir') . '/_sql/migrations';
        }

        foreach ($this->container->getParameter('shopware.plugin_directories') as $pluginDirectory) {
            $path = $pluginDirectory . $pluginName;
            if (file_exists($path)) {
                return $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'migrations';
            }
        }

        return null;
    }

    private function getLatestMigrationVersion(string $migrationFolder): int
    {
        $regexPattern = '/^([0-9]*)-.+\.php$/i';

        $directoryIterator = new \DirectoryIterator($migrationFolder);
        $regex = new \RegexIterator($directoryIterator, $regexPattern, \RecursiveRegexIterator::GET_MATCH);
        $values = iterator_to_array($regex, false);

        if (empty($values)) {
            return 0;
        }

        return (int) max(array_column($values, '1'));
    }

    private function createMigration(string $migrationFolder, string $migrationName, ?string $pluginName, int $nextVersion): string
    {
        $fileName = sprintf('%d-%s.php', $nextVersion, $this->camelCaseToDash($migrationName));

        file_put_contents($migrationFolder . DIRECTORY_SEPARATOR . $fileName, $this->generateMigrationFileContent($pluginName, $nextVersion));

        return $fileName;
    }

    private function camelCaseToDash(string $string): string
    {
        return strtolower(ltrim(preg_replace('/[A-Z]/', '-$0', $string), '-'));
    }

    private function generateMigrationFileContent(?string $pluginName, int $nextVersion): string
    {
        if ($pluginName === null) {
            return $this->createCoreMigration($nextVersion);
        }

        return $this->createPluginMigration($pluginName, $nextVersion);
    }

    private function createCoreMigration(int $nextVersion): string
    {
        return sprintf('<?php
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
class Migrations_Migration%d extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // @todo: Implement up
    }
}
', $nextVersion);
    }

    private function createPluginMigration(string $pluginName, int $nextVersion): string
    {
        return sprintf('<?php

namespace %s\Migrations;

use Shopware\Components\Migrations\AbstractPluginMigration;

class Migration%d extends AbstractPluginMigration
{
    public function up($modus): void
    {
        // @todo: Implement up
    }

    public function down(bool $keepUserData): void
    {
        // @todo: Implement down
    }
}
', $pluginName, $nextVersion);
    }
}
