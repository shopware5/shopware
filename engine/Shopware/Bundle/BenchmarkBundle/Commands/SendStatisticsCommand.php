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

use Shopware\Bundle\BenchmarkBundle\Exception\TransmissionNotNecessaryException;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Benchmark\BenchmarkConfig;
use Shopware\Models\Benchmark\Repository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendStatisticsCommand extends ShopwareCommand
{
    const MAX_BATCH_SIZE = 3000;

    public function configure()
    {
        $this
            ->setName('sw:benchmark:send')
            ->setDescription('Sends statistics data')
            ->addArgument(
                'shopIds',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of shop IDs to send data for. All shops will be considered if this is left empty'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force transmission of data even if selected shops are inactive or data were recently sent'
            )
            ->addOption(
                'batch',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Amount of data to be sent per request. Max 3000'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Repository $benchmarkRepository */
        $benchmarkRepository = $this->getContainer()->get('shopware.benchmark_bundle.repository.config');
        $statisticsService = $this->getContainer()->get('shopware.benchmark_bundle.statistics_transmission');

        $benchmarkRepository->synchronizeShops();

        $shopIds = $input->getArgument('shopIds');
        if (!$shopIds) {
            $shopIds = $benchmarkRepository->getShopIds();
        }

        foreach ($shopIds as $shopId) {
            $shopId = (int) $shopId;
            $shopConfig = $benchmarkRepository->getConfigForShop($shopId);

            if (!$shopConfig) {
                $output->writeln(sprintf('<comment>No shop with ID %s found.</comment>', $shopId));
                continue;
            }

            $locked = $shopConfig->getLocked();
            $dateOneHourAgo = new \DateTime('now');
            $dateOneHourAgo->sub(new \DateInterval('PT1H'));

            if ($locked && $locked > $dateOneHourAgo) {
                $output->writeln(sprintf('<comment>Shop with ID %s is currently locked, skipping.</comment>', $shopId));
                continue;
            }

            // Shop doesn't have to send data. If 'force' parameter is set, this will be ignored.
            if (!$input->getOption('force') && !$this->isShopValid($shopConfig)) {
                $output->writeln(sprintf('<comment>No transmission currently necessary for shop with ID %s. Use \'force\' option if you still want to transmit data for this shop.</comment>', $shopId));
                continue;
            }

            $benchmarkRepository->lockShop($shopId);

            $output->writeln(sprintf('Sending statistics for shop with ID %s...', $shopId));

            try {
                $batchSize = null;

                if ($input->getOption('batch')) {
                    $batchSize = $input->getOption('batch');

                    if ($batchSize > $this::MAX_BATCH_SIZE) {
                        $batchSize = $this::MAX_BATCH_SIZE;
                    }
                }

                while (true) {
                    $statisticsService->transmit($shopConfig, $batchSize);
                }
            } catch (TransmissionNotNecessaryException $e) {
                $output->writeln('<info>Done!</info>');
            } finally {
                $benchmarkRepository->unlockShop($shopId);
            }
        }
    }

    /**
     * @return bool
     */
    private function isShopValid(BenchmarkConfig $shopConfig)
    {
        return $shopConfig->isActive()
            && $shopConfig->getLastSent()->add(new \DateInterval('P1D')) < new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
