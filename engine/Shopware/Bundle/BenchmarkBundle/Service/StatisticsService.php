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

use Shopware\Bundle\BenchmarkBundle\StatisticsClientInterface;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class StatisticsService
{
    /**
     * @var StatisticsClientInterface
     */
    private $statisticsClient;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    /**
     * @param StatisticsClientInterface $statisticsClient
     * @param BenchmarkRepository       $benchmarkRepository
     */
    public function __construct(
        StatisticsClientInterface $statisticsClient,
        BenchmarkRepository $benchmarkRepository)
    {
        $this->statisticsClient = $statisticsClient;
        $this->benchmarkRepository = $benchmarkRepository;
    }

    public function transmit()
    {
        $config = $this->benchmarkRepository->getMainConfig();

        /** @var StatisticsResponse $response */
        $response = $this->statisticsClient->fetchStatistics(new StatisticsRequest($config->getToken()));

        $config->setCachedTemplate($response->getHtml());
        $config->setLastReceived($response->getDateTime());

        $this->benchmarkRepository->save($config);

        return $response;
    }
}
