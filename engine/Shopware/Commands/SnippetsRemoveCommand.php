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

namespace Shopware\Commands;

use RuntimeException;
use Shopware\Components\Snippet\DatabaseHandler;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SnippetsRemoveCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'folder') {
            $rootDir = $this->container->getParameter('kernel.root_dir');

            if (!\is_string($rootDir)) {
                throw new RuntimeException('Parameter kernel.root_dir has to be an string');
            }

            return $this->completeInDirectory($rootDir);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:snippets:remove')
            ->setDescription('Remove snippets from the database for a specific folder')
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'The folder to search for snippets'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->container->getParameter('kernel.root_dir');

        if (!\is_string($rootDir)) {
            throw new RuntimeException('Parameter kernel.root_dir has to be an string');
        }

        $folder = $input->getArgument('folder');
        if (!\is_string($folder)) {
            throw new RuntimeException('Argument "folder" needs to be a string');
        }

        $folder = $rootDir . '/' . $folder . '/';

        $databaseLoader = $this->container->get(DatabaseHandler::class);
        $databaseLoader->setOutput($output);
        $databaseLoader->removeFromDatabase($folder);

        return 0;
    }
}
