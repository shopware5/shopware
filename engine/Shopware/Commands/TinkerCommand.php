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

use Psy\Configuration;
use Psy\Shell;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TinkerCommand extends ShopwareCommand
{
    protected function configure()
    {
        $this->addArgument('include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->setCatchExceptions(false);

        $config = new Configuration([
            'updateCheck' => 'never',
        ]);

        $shell = new Shell($config);
        $shell->addCommands($this->getCommands());
        $shell->setIncludes($input->getArgument('include'));
        $shell->run();
    }

    protected function getCommands()
    {
        $commands = $this->getApplication()->all();

        foreach ($commands as $command) {
            if ($command instanceof ContainerAwareInterface) {
                $command->setContainer($this->container);
            }
        }

        return $commands;
    }
}
