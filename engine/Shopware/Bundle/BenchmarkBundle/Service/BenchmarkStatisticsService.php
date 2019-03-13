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

namespace Shopware\Bundle\BenchmarkBundle\Service;

use DateInterval;
use Shopware\Bundle\BenchmarkBundle\Exception\TransmissionNotNecessaryException;
use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkDataResult;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class BenchmarkStatisticsService
{
    /**
     * @var StatisticsService
     */
    private $statistics;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    /**
     * @var DateInterval
     */
    private $interval;

    /**
     * @var BusinessIntelligenceService
     */
    private $biService;

    /**
     * @throws \Exception
     */
    public function __construct(
        BenchmarkRepository $benchmarkRepository,
        StatisticsService $statistics,
        BusinessIntelligenceService $biService,
        DateInterval $interval = null
    ) {
        $this->benchmarkRepository = $benchmarkRepository;
        $this->statistics = $statistics;
        $this->biService = $biService;
        $this->interval = $interval ?: new DateInterval('P1D');
    }

    /**
     * @return BenchmarkDataResult
     */
    public function handleTransmission()
    {
        $statisticsResponse = $this->sendStatisticsData();
        $biResponse = $this->fetchBenchmarkData();

        return new BenchmarkDataResult($statisticsResponse, $biResponse);
    }

    private function sendStatisticsData()
    {
        // Configuration hasn't been done yet
        if ($this->benchmarkRepository->getConfigsCount() === 0) {
            return null;
        }

        $this->benchmarkRepository->synchronizeShops();

        $benchmarkConfig = $this->benchmarkRepository->getNextTransmissionShopConfig();

        if (!$benchmarkConfig) {
            return null;
        }

        $this->benchmarkRepository->lockShop($benchmarkConfig->getShopId());

        $statisticsResponse = null;

        try {
            $statisticsResponse = $this->statistics->transmit($benchmarkConfig, $benchmarkConfig->getBatchSize());
            $statisticsResponse->setShopId((int) $benchmarkConfig->getShopId());
        } catch (TransmissionNotNecessaryException $e) {
            return null;
        } finally {
            $this->benchmarkRepository->unlockShop($benchmarkConfig->getShopId());
        }

        return $statisticsResponse;
    }

    private function fetchBenchmarkData()
    {
        $biResponse = null;

        $benchmarkConfig = $this->benchmarkRepository->getNextReceivingShopConfig();

        if (!$benchmarkConfig) {
            return null;
        }

        $biResponse = $this->biService->transmit($benchmarkConfig);
        $biResponse->setShopId((int) $benchmarkConfig->getShopId());

        return $biResponse;
    }
}
