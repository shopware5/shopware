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
use Shopware\Models\Benchmark\BenchmarkConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReceiveStatisticsCommand extends ShopwareCommand
{
    public function configure()
    {
        $this
            ->setName('sw:benchmark:receive')
            ->setDescription('Receives statistics data')
            ->addArgument(
                'shopIds',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of shop IDs to receive benchmark statistics for. All shops will be considered if this is left empty'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force transmission of data even if selected shops are inactive or data were recently received'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $benchmarkRepository = $this->getContainer()->get('shopware.benchmark_bundle.repository.config');
        $businessIntelligenceService = $this->getContainer()->get('shopware.benchmark_bundle.bi_transmission');

        $shopIds = $input->getArgument('shopIds');
        if (!$shopIds) {
            $shopIds = $benchmarkRepository->getShopIds();
        }

        foreach ($shopIds as $shopId) {
            $shopConfig = $benchmarkRepository->getConfigForShop($shopId);

            if (!$shopConfig) {
                $output->writeln(sprintf('<comment>No shop with ID %s found.</comment>', $shopId));
                continue;
            }

            // Shop just received data. If 'force' parameter is set, this will be ignored.
            if (!$input->getOption('force') && !$this->isShopValid($shopConfig)) {
                $output->writeln(sprintf('<comment>No receiving of the benchmark statistics currently necessary for shop with ID %s. Use \'force\' option if you still want to receive the benchmark statistics for this shop.</comment>', $shopId));
                continue;
            }

            $output->writeln(sprintf('Receiving benchmark statistics for shop with ID %s...', $shopId));
            $businessIntelligenceService->transmit($shopConfig);
            $output->writeln('<info>Done!</info>');
        }
    }

    /**
     * @return bool
     */
    private function isShopValid(BenchmarkConfig $shopConfig)
    {
        return $shopConfig->isActive()
            && $shopConfig->getLastReceived()->add(new \DateInterval('P1D')) < new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
