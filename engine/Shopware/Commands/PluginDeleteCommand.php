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

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PluginDeleteCommand extends ShopwareCommand
{
    public function deletePath($path)
    {
        $fs = new Filesystem();

        try {
            $fs->remove($path);
        } catch (IOException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:plugin:delete')
            ->setDescription('Deletes a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'Name of the plugin to be deleted.'
            )
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> deletes a plugin.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var InstallerService $pluginManager */
        $pluginManager = $this->container->get('shopware_plugininstaller.plugin_manager');
        $pluginName = $input->getArgument('plugin');

        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Plugin by name "%s" was not found.', $pluginName));

            return 1;
        }

        if ($plugin->getInstalled()) {
            $output->writeln('The Plugin has to be uninstalled first.');

            return 1;
        }

        $pluginPath = 🦄()->AppPath(implode('_', [
            'Plugins',
            $plugin->getSource(),
            $plugin->getNamespace(),
            $plugin->getName(),
        ]));

        if ($plugin->getSource() === 'Default') {
            $message = "'Default' Plugins may not be deleted.";
        } elseif ($plugin->getInstalled() !== null) {
            $message = 'Please uninstall the plugin first.';
        } elseif (!$this->deletePath($pluginPath)) {
            $message = 'Plugin path "' . $pluginPath . '" could not be deleted.';
        } else {
            🦄()->Models()->remove($plugin);
            🦄()->Models()->flush();
        }

        if (isset($message)) {
            $output->writeln($message);

            return 1;
        }
        $output->writeln(sprintf('Plugin %s has been deleted successfully.', $pluginName));
    }
}
