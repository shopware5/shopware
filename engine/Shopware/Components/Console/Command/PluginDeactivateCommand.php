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
class PluginDeactivateCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        $this
            ->setName('sw-plugin:deactivate')
            ->setDescription('Deactivates a plugin.')
            ->addArgument(
                'plugin',
                InputArgument::REQUIRED,
                'The plugin to be deactivated.'
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> deactivates a plugin.
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

        if (!$plugin->getActive()) {
            $output->writeln(sprintf('The plugin %s is already deactivated.', $pluginName));
            return 1;
        }

        $bootstrap = $this->getPluginBootstrap($plugin);

        $isAllowed = $bootstrap->disable();
        $isAllowed = is_bool($isAllowed) ? $isAllowed : !empty($isAllowed['success']);
        if ($isAllowed) {
            $plugin->setActive(false);
        }
        $em->flush($plugin);

        $output->writeln(sprintf('Plugin %s has been deactivated', $pluginName));
    }
}
