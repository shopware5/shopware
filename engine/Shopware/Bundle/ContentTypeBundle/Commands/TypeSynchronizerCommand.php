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

namespace Shopware\Bundle\ContentTypeBundle\Commands;

use RuntimeException;
use Shopware\Bundle\ContentTypeBundle\Services\DatabaseContentTypeSynchronizerInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TypeSynchronizerCommand extends ShopwareCommand
{
    public function configure(): void
    {
        $this->setDescription('Synchronizes contenttypes from XML files to database')
            ->addOption('destructive', 'd', InputOption::VALUE_NONE, 'Remove unused tables and columns of types');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sync = $this->container->get(DatabaseContentTypeSynchronizerInterface::class);

        $activePlugins = $this->container->getParameter('active_plugins');

        if (!\is_array($activePlugins)) {
            throw new RuntimeException('Parameter active_plugins needs to be an array');
        }

        $types = $sync->sync(array_keys($activePlugins), $input->getOption('destructive'));
        $io = new SymfonyStyle($input, $output);

        $io->success(sprintf('Synchronized %d type(s)', \count($types)));

        return 0;
    }
}
