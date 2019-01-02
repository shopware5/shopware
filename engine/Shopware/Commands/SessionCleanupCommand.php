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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SessionCleanupCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:session:cleanup')
            ->setDescription('Removes expired sessions')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command removes expired sessions.
This is most useful combined with <comment>gc_probability: 0</comment> to disable session garbage collection during runtime.
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $saveHandler = $this->container->get('session.save_handler');
        if ($saveHandler === null) {
            $io->error('Save handler not available');

            return 1;
        }

        $options = $this->container->getParameter('shopware.session');
        $maxlifetime = (int) (isset($options['gc_maxlifetime']) ? $options['gc_maxlifetime'] : ini_get('session.gc_maxlifetime'));

        $saveHandler->gc($maxlifetime);
        $saveHandler->close();

        $io->success('Successfully removed expired sessions');
    }
}
