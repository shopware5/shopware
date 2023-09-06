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
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnippetsToIniCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'target') {
            return $this->completeDirectoriesInDirectory();
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'locale') {
            return $this->completeInstalledLocaleKeys($context->getCurrentWord());
        }

        return [];
    }

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
        $target = $input->getOption('target');
        if ($target !== null && !\is_string($target)) {
            $target = null;
        }
        $dir = $this->container->get('application')->DocPath($target);
        if (!file_exists($dir) || !is_writable($dir)) {
            $old = umask(0);
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
            chmod($dir, 0777);
            umask($old);
        }
        if (!is_writable($dir)) {
            $output->writeln('<error>Output dir ' . $input->getOption('file') . ' is not writable, aborting</error>');

            return 1;
        }

        /** @var \Shopware\Components\Snippet\DatabaseHandler $databaseLoader */
        $databaseLoader = $this->container->get(\Shopware\Components\Snippet\DatabaseHandler::class);
        $databaseLoader->setOutput($output);
        $databaseLoader->dumpFromDatabase($input->getOption('target'), $input->getArgument('locale'));

        return 0;
    }
}
