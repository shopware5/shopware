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
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnippetsToDbCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'source') {
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
            ->setName('sw:snippets:to:db')
            ->setDescription('Load snippets from .ini files into database')
            ->addOption(
                'include-plugins',
                null,
                InputOption::VALUE_NONE,
                'If given, the active plugin snippets will also be loaded'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If given, the file will be overwritten if it already exists'
            )
            ->addOption(
                'source',
                null,
                InputOption::VALUE_REQUIRED,
                'The folder from where the snippets should be imported, relative to Shopware\'s root folder',
                'snippets'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHandler $databaseLoader */
        $databaseLoader = $this->container->get(DatabaseHandler::class);
        $force = $input->getOption('force');

        $rootDir = $this->container->getParameter('kernel.root_dir');

        if (!\is_string($rootDir)) {
            throw new RuntimeException('Parameter kernel.root_dir has to be an string');
        }

        $source = $input->getOption('source');
        if (!\is_string($source)) {
            throw new RuntimeException('Option "source" needs to be a string');
        }

        $sourceDir = $rootDir . '/' . $source . '/';

        $databaseLoader->setOutput($output);
        $databaseLoader->loadToDatabase($sourceDir, $force);

        // Import plugin snippets
        if ($input->getOption('include-plugins')) {
            $pluginRepository = $this->container->get('shopware.model_manager')->getRepository(Plugin::class);

            /** @var Plugin[] $plugins */
            $plugins = $pluginRepository->findBy(['active' => true]);

            $pluginDirectories = $this->container->getParameter('shopware.plugin_directories');

            if (!\is_array($pluginDirectories)) {
                throw new RuntimeException('Parameter shopware.plugin_directories has to be an array');
            }

            foreach ($plugins as $plugin) {
                if (\array_key_exists($plugin->getSource(), $pluginDirectories)) {
                    $pluginPath = $pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . '/' . $plugin->getName();

                    $databaseLoader->loadToDatabase($pluginPath . '/Snippets/', $force);
                    $databaseLoader->loadToDatabase($pluginPath . '/snippets/', $force);
                    $databaseLoader->loadToDatabase($pluginPath . '/Resources/snippet/', $force);

                    $output->writeln('<info>Importing snippets for ' . $plugin->getName() . ' plugin</info>');
                }

                if ($plugin = $this->getPlugin($plugin->getName())) {
                    $databaseLoader->loadToDatabase($plugin->getPath() . '/Resources/snippets/', $force);

                    $output->writeln('<info>Importing snippets for ' . $plugin->getName() . ' plugin</info>');
                }
            }
            $output->writeln('<info>Plugin snippets processed correctly</info>');
        }

        return 0;
    }

    /**
     * @param string $pluginName
     *
     * @return \Shopware\Components\Plugin|null
     */
    private function getPlugin($pluginName)
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');
        $plugins = $kernel->getPlugins();

        if (!\array_key_exists($pluginName, $plugins)) {
            return null;
        }

        return $plugins[$pluginName];
    }
}
