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

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronListCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:cron:list')
            ->setDescription('Lists cronjobs.')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> lists cronjobs.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->load('table');

        /** @var \Enlight_Components_Cron_Manager $manager */
        $manager = $this->container->get('cron');
        $rows = [];

        foreach ($manager->getAllJobs() as $job) {
            $rows[] = [
                $job->getName(),
                $job->getAction(),
                $job->getActive() ? 'Yes' : 'No',
                $job->getInterval(),
                $job->getNext(),
                $job->getEnd(),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Action', 'Active', 'Interval', 'Next run', 'Last run'])
              ->setRows($rows);

        $table->render();
    }
}
