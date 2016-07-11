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

use Shopware\Components\Snippet\QueryHandler;
use Shopware\Models\Plugin\Plugin;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SnippetsToSqlCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:snippets:to:sql')
            ->setDescription('Load snippets from .ini files into sql file')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Target file'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If given, the file will be overwritten if it already exists'
            )
            ->addOption(
                'include-default-plugins',
                null,
                InputOption::VALUE_NONE,
                'If given, default plugin snippets will also be loaded. No database connection required.'
            )
            ->addOption(
                'include-plugins',
                null,
                InputOption::VALUE_NONE,
                'If given, the active plugin snippets will also be loaded. Database connection required.'
            )
            ->addOption(
                'update',
                null,
                InputOption::VALUE_REQUIRED,
                'If false, updates on existing snippets will not be performed'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists($input->getArgument('file')) && !$input->getOption('force')) {
            $output->writeln('<error>Output file '.$input->getArgument('file').' already exists, aborting</error>');

            return 1;
        }

        $output->writeln(sprintf('<info>Writing to file "%s".</info>', $input->getArgument('file')));

        /** @var $queryLoader QueryHandler */
        $queryLoader = $this->container->get('shopware.snippet_query_handler');

        $this->exportCoreSnippets($input, $output, $queryLoader);

        if ($input->getOption('include-default-plugins')) {
            $this->exportDefaultPlugins($input, $output, $queryLoader);
        }

        if ($input->getOption('include-plugins')) {
            $this->exportPlugins($input, $output, $queryLoader);
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QueryHandler    $queryLoader
     */
    private function exportCoreSnippets(InputInterface $input, OutputInterface $output, QueryHandler $queryLoader)
    {
        $queries = $queryLoader->loadToQuery(null, $input->getOption('update') !== 'false');
        file_put_contents($input->getArgument('file'), implode(PHP_EOL, $queries));
        $output->writeln('<info>Core snippets processed correctly</info>');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QueryHandler    $queryLoader
     */
    protected function exportDefaultPlugins(InputInterface $input, OutputInterface $output, QueryHandler $queryLoader)
    {
        $pluginDirectories = $this->container->getParameter('shopware.plugin_directories');
        $pluginBasePath = $pluginDirectories['Default'];

        foreach (array('Backend', 'Core', 'Frontend') as $namespace) {
            /** @var $pluginDir \SplFileInfo */
            foreach (new \DirectoryIterator($pluginBasePath . $namespace) as $pluginDir) {
                if ($pluginDir->isDot() || !$pluginDir->isDir()) {
                    continue;
                }

                $output->writeln('<info>Importing snippets for ' .$pluginDir->getBasename().' plugin</info>');
                $this->exportPluginSnippets($queryLoader, $pluginDir->getPathname(), $input->getArgument('file'));
            }
        }

        $output->writeln('<info>Default Plugin snippets processed correctly</info>');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QueryHandler    $queryLoader
     */
    protected function exportPlugins(InputInterface $input, OutputInterface $output, QueryHandler $queryLoader)
    {
        $pluginRepository = $this->container->get('shopware.model_manager')->getRepository(
            'Shopware\Models\Plugin\Plugin'
        );

        /** @var Plugin[] $plugins */
        $plugins = $pluginRepository->findBy(['active' => true]);

        $pluginDirectories = $this->container->getParameter('shopware.plugin_directories');

        foreach ($plugins as $plugin) {
            $pluginPath = $pluginDirectories[$plugin->getSource()] . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();

            $output->writeln('<info>Importing snippets for '.$plugin->getName().' plugin</info>');
            $this->exportPluginSnippets($queryLoader, $pluginPath, $input->getArgument('file'));
        }
        $output->writeln('<info>Plugin snippets processed correctly</info>');
    }

    /**
     * @param QueryHandler $queryLoader
     * @param string       $path
     * @param string       $file
     */
    private function exportPluginSnippets(QueryHandler $queryLoader, $path, $file)
    {
        $queries = array_merge(
            $queryLoader->loadToQuery($path.'/Snippets/'),
            $queryLoader->loadToQuery($path.'/snippets/'),
            $queryLoader->loadToQuery($path.'/Resources/snippet/')
        );

        file_put_contents($file, implode(PHP_EOL, $queries), FILE_APPEND);
    }
}
