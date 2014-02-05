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

use Symfony\Component\Console\Input\InputArgument;
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
                'include-plugins',
                null,
                InputOption::VALUE_NONE,
                'If given, the active plugin snippets will also be loaded'
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

        /** @var $queryLoader \Shopware\Components\Snippet\QueryHandler */
        $queryLoader = $this->container->get('shopware.snippet_query_handler');

        //Import core snippets
        $queries = $queryLoader->loadToQuery(null, $input->getOption('update') !== 'false');
        file_put_contents($input->getArgument('file'), implode(PHP_EOL, $queries));
        $output->writeln('<info>Core snippets processed correctly</info>');

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
                $queries = array_merge(
                    $queryLoader->loadToQuery($pluginPath.'/Snippets/'),
                    $queryLoader->loadToQuery($pluginPath.'/snippets/'),
                    $queryLoader->loadToQuery($pluginPath.'/Resources/snippet/')
                );
                file_put_contents($input->getArgument('file'), implode(PHP_EOL, $queries), FILE_APPEND);
            }
            $output->writeln('<info>Plugin snippets processed correctly</info>');
        }
    }
}
