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

namespace Shopware\Bundle\BenchmarkBundle\Commands;

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TransmitStatisticsCommand extends ShopwareCommand
{
    public function configure()
    {
        $this
            ->setName('sw:benchmark:execute')
            ->setDescription('Transmits statistics data and retrieves benchmark (when activated)')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force transmission of data even if time interval hasn\'t passed yet')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $benchmarkRepository = $this->container->get('shopware.benchmark_bundle.repository.config');

        $shopConfigs = $benchmarkRepository->getShopConfigs();
        foreach ($shopConfigs as $config) {
            if (!$config['active']) {
                $output->writeln(sprintf('<comment>Benchmarks for shop with ID %s are deactivated, doing nothing.</comment>', $config['shopId']));
                continue;
            }

            $lastReceived = \DateTime::createFromFormat('Y-m-d H:i:s', $config['lastReceived']);
            if (!$input->getOption('force') &&
                $lastReceived->add(new \DateInterval('P1D')) > new \DateTime('now', new \DateTimeZone('UTC'))) {
                $output->writeln(sprintf('<comment>No retrieval currently necessary for shop with ID %s.</comment>', $config['shopId']));
                continue;
            }

            $lastSent = \DateTime::createFromFormat('Y-m-d H:i:s', $config['lastSent']);
            if (!$input->getOption('force') &&
                $lastSent->add(new \DateInterval('P1D')) > new \DateTime('now', new \DateTimeZone('UTC'))) {
                $output->writeln(sprintf('<comment>No transmission currently necessary for shop with ID %s.</comment>', $config['shopId']));
                continue;
            }

            $output->write(sprintf('Transmitting statistics for shop with ID %s...', $config['shopId']));
            $statistics = $this->container->get('shopware.benchmark_bundle.statistics_transmission');
            $statistics->transmit($benchmarkRepository->getConfigForShop($config['shopId']));
            $output->writeln('<info>done!</info>');

            $output->write(sprintf('Retrieving business intelligence for shop with ID %s...', $config['shopId']));
            $benchmark = $this->container->get('shopware.benchmark_bundle.bi_transmission');
            $benchmark->transmit($benchmarkRepository->getConfigForShop($config['shopId']));
            $output->writeln('<info>done!</info>');
        }
    }
}
