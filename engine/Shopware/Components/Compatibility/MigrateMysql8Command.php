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

namespace Shopware\Components\Compatibility;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateMysql8Command extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:migrate:mysql8')
            ->setDescription('Executes necessary migration steps to support MySQL 8 completely')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force migration for MySQL8 support'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $legacyDocumentIdConverter = $this->container->get('legacy_documentid_converter');

        $isMigrationNecessary = $legacyDocumentIdConverter->isDocumentIdUpperCase();
        if (!$isMigrationNecessary) {
            $output->writeln('<info>Migration to MySQL8 compatibility already happened!</info>');
            exit(1);
        }

        if (!(bool) $input->getOption('force')) {
            $io = new SymfonyStyle($input, $output);
            $output->writeln('<comment>Migration for MySQL 8 support necessary, use -f to migrate now.</comment>');
            $output->writeln('<comment>Column `ID` in `s_core_documents` will be changed to lower case (`id`).</comment>');

            $io->caution('BACKUP YOUR DATABASE BEFORE MIGRATING THE SCHEMA TO SUPPORT MySQL 8!');
            $io->caution('You won\'t be able to downgrade your Shopware installation to a version < 5.5 without restoring your database backup first!');

            exit(1);
        }

        $legacyDocumentIdConverter->migrateTable();

        $output->writeln('<info>Migration for MySQL 8 executed! Column `id` in `s_core_documents` is now lowercase.</info>');
    }
}
