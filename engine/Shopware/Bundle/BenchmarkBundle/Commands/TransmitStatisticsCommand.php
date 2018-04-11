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

        $config = $benchmarkRepository->getMainConfig();

        if (!$config->isActive()) {
            $output->writeln('<comment>Benchmarks are deactivated, doing nothing.</comment>');
            exit(1);
        }

        if (!$config->isTermsAccepted()) {
            $output->writeln('<comment>Terms of service haven\'t been accepted yet.</comment>');
            exit(1);
        }

        if (!$input->getOption('force') &&
            $config->getLastReceived()->add(new \DateInterval('P1D')) > new \DateTime('now', new \DateTimeZone('UTC'))) {
            $output->writeln('<comment>No retrieval currently necessary.</comment>');
            exit(1);
        }

        if (!$input->getOption('force') &&
            $config->getLastSent()->add(new \DateInterval('P1D')) > new \DateTime('now', new \DateTimeZone('UTC'))) {
            $output->writeln('<comment>No transmission currently necessary.</comment>');
            exit(1);
        }

        $output->write('Transmitting statistics...');
        $statistics = $this->container->get('shopware.bundle_benchmark.statistics_transmission');
        $statistics->transmit();
        $output->writeln('<info>done!</info>');

        $output->write('Retrieving benchmarks...');
        $benchmark = $this->container->get('shopware.bundle_benchmark.benchmark_transmission');
        $benchmark->transmit();
        $statistics = $this->container->get('shopware.bundle_benchmark.statistics_transmission');
        $statistics->transmit();
        $output->writeln('<info>done!</info>');
    }
}
