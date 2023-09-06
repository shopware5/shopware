<?php
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

use Shopware\Commands\ShopwareCommand;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImageMigrateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        /** @var array<string, array> $cdnAdapters */
        $cdnAdapters = $this->getContainer()->getParameter('shopware.cdn.adapters');

        if (\in_array($optionName, ['from', 'to'])) {
            return array_keys($cdnAdapters);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:media:migrate')
            ->setDescription('Migrate images to new structure')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL)
            ->addOption('to', null, InputOption::VALUE_OPTIONAL)
            ->addOption('skip-scan', null, InputOption::VALUE_NONE, 'Skips the initial filesystem scan and migrates the files immediately.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getOption('from') ?: 'local';
        $to = $input->getOption('to') ?: 'local';
        $skipScan = $input->getOption('skip-scan');

        $filesystemFactory = $this->getContainer()->get(\Shopware\Bundle\MediaBundle\MediaServiceFactory::class);
        $fromFileSystem = $filesystemFactory->factory($from);
        $toFileSystem = $filesystemFactory->factory($to);

        $mediaMigration = $this->getContainer()->get('shopware_media.media_migration');
        $mediaMigration->migrate($fromFileSystem, $toFileSystem, $output, $skipScan);

        return 0;
    }
}
