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

namespace Shopware\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
            ->setDescription('Load snippets from .ini files into sql')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Where to dump the snippet information',
                'database'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Target file'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'If given, the file will be overwritten if it already exists'
            )
            ->addOption(
                'include-plugins',
                null,
                InputOption::VALUE_NONE,
                'If given, the active plugin snippets will also be loaded'
            )
            ->addOption(
                'update',
                null,
                InputOption::VALUE_REQUIRED,
                'Only applicable to file export. If false, updates on existing snippets will not be performed'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Import core snippets
        if ($input->getOption('target') == 'database') {
            $databaseLoader = $this->container->get('shopware.snippet_database_handler');
            $databaseLoader->setOutput($output);
            $databaseLoader->loadToDatabase();
        } elseif ($input->getOption('target') == 'file') {

            /** @var $queryLoader \Shopware\Components\Snippet\QueryHandler */
            $queryLoader = $this->container->get('shopware.snippet_query_handler');

            $output->writeln('<info>Writing to file</info>');
            if (!$input->getOption('file')) {
                $output->writeln('<error>Target type file requires that you specify the "file" option</error>');
                return;
            }
            $queries = $queryLoader->loadToQuery(null, $input->getOption('update') !== 'false');
            if (file_exists($input->getOption('file')) && !$input->getOption('force')) {
                $output->writeln('<error>Output file '.$input->getOption('file').' already exists, aborting</error>');
                return;
            }
            file_put_contents($input->getOption('file'), implode(PHP_EOL, $queries));
            $output->writeln('<info>Core snippets processed correctly</info>');
        }

        //Import plugin snippets
        if ($input->getOption('include-plugins')) {
            $pluginRepository = $this->container->get('shopware.model_manager')->getRepository('Shopware\Models\Plugin\Plugin');
            $plugins = $pluginRepository->findByActive(true);
            $pluginBasePath = $this->container->get('application')->AppPath('Plugins');

            foreach ($plugins as $plugin) {
                $pluginPath = implode('/', array(
                    rtrim($pluginBasePath, '/'),
                    $plugin->getSource(),
                    $plugin->getNamespace(),
                    $plugin->getName()
                ));

                $output->writeln('<info>Importing snippets for '.$plugin->getName().' plugin</info>');
                if ($input->getOption('target') == 'database') {
                    $databaseLoader->loadToDatabase($pluginPath.'/Snippets/');
                    $databaseLoader->loadToDatabase($pluginPath.'/Resources/snippet/');
                } elseif ($input->getOption('target') == 'file') {
                    $queries = array_merge(
                        $queryLoader->loadToQuery($pluginPath.'/Snippets/'),
                        $queryLoader->loadToQuery($pluginPath.'/Resources/snippet/')
                    );
                    file_put_contents($input->getOption('file'), implode(PHP_EOL, $queries), FILE_APPEND);
                }
            }
            $output->writeln('<info>Plugin snippets processed correctly</info>');
        }
    }
}
