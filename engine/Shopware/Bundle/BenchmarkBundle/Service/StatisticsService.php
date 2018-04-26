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

use Shopware\Bundle\BenchmarkBundle\BenchmarkCollector;
use Shopware\Bundle\BenchmarkBundle\StatisticsClientInterface;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class StatisticsService
{
    /**
     * @var BenchmarkCollector
     */
    private $benchmarkCollector;

    /**
     * @var StatisticsClientInterface
     */
    private $statisticsClient;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    /**
     * @param BenchmarkCollector        $benchmarkCollector
     * @param StatisticsClientInterface $statisticsClient
     * @param BenchmarkRepository       $benchmarkRepository
     */
    public function __construct(
        BenchmarkCollector $benchmarkCollector,
        StatisticsClientInterface $statisticsClient,
        BenchmarkRepository $benchmarkRepository)
    {
        $this->benchmarkCollector = $benchmarkCollector;
        $this->statisticsClient = $statisticsClient;
        $this->benchmarkRepository = $benchmarkRepository;
    }

    /**
     * @return StatisticsResponse
     */
    public function transmit()
    {
        $this->benchmarkCollector->get();

        $request = new StatisticsRequest($this->benchmarkCollector->get());

        /** @var StatisticsResponse $statisticsResponse */
        $statisticsResponse = $this->statisticsClient->sendStatistics($request);

        $config = $this->benchmarkRepository->getMainConfig();
        $config->setLastSent($statisticsResponse->getDateUpdated());
        $config->setToken($statisticsResponse->getToken());

        $this->benchmarkRepository->save($config);

        return $statisticsResponse;
    }
}
