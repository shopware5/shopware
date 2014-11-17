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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @category  Shopware
 * @package   Shopware\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SnippetsToIniCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:snippets:to:ini')
            ->setDescription('Dump snippets from the database into .ini files')
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Locale to be exported.'
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'The folder where the exported files should be placed. Defaults to snippetsExport',
                'snippetsExport'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $this->container->get('application')->DocPath($input->getOption('target'));
        if (!file_exists($dir) || !is_writeable($dir)) {
            $old = umask(0);
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
            umask($old);
        }
        if (!is_writeable($dir)) {
            $output->writeln('<error>Output dir '.$input->getOption('file').' is not writable, aborting</error>');
            return 1;
        }

        /** @var $databaseLoader \Shopware\Components\Snippet\DatabaseHandler */
        $databaseLoader = $this->container->get('shopware.snippet_database_handler');
        $databaseLoader->setOutput($output);
        $databaseLoader->dumpFromDatabase($input->getOption('target'), $input->getArgument('locale'));
    }
}
