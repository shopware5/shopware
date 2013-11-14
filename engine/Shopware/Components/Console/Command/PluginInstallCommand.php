<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\Console\Command;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class PluginInstallCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this
            ->setName('sw-plugin:install')
            ->setDescription('Installs a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'The plugin to be installed.'
            )
            ->addOption(
                'activate',
                null,
                InputOption::VALUE_NONE,
                'Activate plugin after intallation.'
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> installs a plugin.
EOF
            );
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        $pluginName = $input->getArgument('plugin');

        $plugin = $this->getPluginByName($pluginName);
        if ($plugin === null) {
            $output->writeln(sprintf('Unknown plugin: %s.', $pluginName));
            return 1;
        }

        if ($plugin->getInstalled()) {
            $output->writeln(sprintf('The plugin %s is already installed.', $pluginName));
            return 1;
        }

        $bootstrap = $this->getPluginBootstrap($plugin);
        /** @var $namespace \Shopware_Components_Plugin_Namespace */
        $namespace = $bootstrap->Collection();

        try {
            $result = $namespace->installPlugin($bootstrap);
        } catch (\Exception $e) {
            $output->writeln(sprintf("Unable to install, got exception:\n%s\n", $e->getMessage()));
            return 1;
        }

        $success = (is_bool($result) && $result || isset($result['success']) && $result['success']);
        if (!$success) {
            if (isset($result['message'])) {
                $output->writeln(sprintf("Unable to install, got message:\n%s\n", $result['message']));
            } else {
                $output->writeln(sprintf('Unable to install %s, an unknown error occured.', $pluginName));
            }

            return 1;
        }

        $output->writeln(sprintf('Plugin %s has been installed successfully.', $pluginName));
        if (!$input->getOption('activate')) {
            return;
        }

        $isAllowed = $bootstrap->enable();
        $isAllowed = is_bool($isAllowed) ? $isAllowed : !empty($isAllowed['success']);
        if ($isAllowed) {
            $plugin->setActive(true);
        }
        $em->flush($plugin);

        $output->writeln(sprintf('Plugin %s has been activated successfully.', $pluginName));
    }
}
