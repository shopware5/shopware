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

use Shopware\Components\Snippet\SnippetValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SnippetsValidateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:snippets:validate')
            ->setDescription('Validates .ini files containing snippets')
            ->addArgument(
                'folder',
                InputArgument::OPTIONAL,
                'The folder to search for snippets. If empty, scans core and all default plugins'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('folder');

        /** @var SnippetValidator $validator */
        $validator = $this->container->get('shopware.snippet_validator');

        if (empty($argument)) {
            $invalidPaths = $validator->validate($this->container->getParameter('kernel.root_dir') . '/snippets');
            $invalidPaths = array_merge(
                $invalidPaths,
                $this->validatePlugins($validator)
            );
        } else {
            $invalidPaths = $validator->validate($argument);
        }

        if (empty($invalidPaths)) {
            $output->writeln('<info>All snippets are correctly defined</info>');
        } else {
            $output->writeln('<error>The following errors occurred:</error>');
            foreach ($invalidPaths as $error) {
                $output->writeln('<error>' . $error . '</error>');
            }
        }
    }

    /**
     * @param SnippetValidator $validator
     *
     * @throws \Exception
     *
     * @return \string[]
     */
    protected function validatePlugins(SnippetValidator $validator)
    {
        $invalidPaths = [];

        $pluginDirectories = $this->container->getParameter('shopware.plugin_directories');
        foreach ($pluginDirectories as $pluginBasePath) {
            foreach (['Backend', 'Core', 'Frontend'] as $namespace) {
                foreach (new \DirectoryIterator($pluginBasePath . $namespace) as $pluginDir) {
                    if ($pluginDir->isDot() || !$pluginDir->isDir()) {
                        continue;
                    }

                    $invalidPaths = array_merge($invalidPaths, $validator->validate($pluginDir->getPathname()));
                }
            }
        }

        return $invalidPaths;
    }
}
