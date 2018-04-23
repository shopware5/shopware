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

use Shopware\Bundle\BenchmarkBundle\BenchmarkClientInterface;
use Shopware\Bundle\BenchmarkBundle\BenchmarkCollector;
use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkRequest;
use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkResponse;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class BenchmarkService
{
    /**
     * @var BenchmarkCollector
     */
    private $benchmarkCollector;

    /**
     * @var BenchmarkClientInterface
     */
    private $benchmarkClient;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    /**
     * @param BenchmarkCollector       $benchmarkCollector
     * @param BenchmarkClientInterface $benchmarkClient
     * @param BenchmarkRepository      $benchmarkRepository
     */
    public function __construct(
        BenchmarkCollector $benchmarkCollector,
        BenchmarkClientInterface $benchmarkClient,
        BenchmarkRepository $benchmarkRepository)
    {
        $this->benchmarkCollector = $benchmarkCollector;
        $this->benchmarkClient = $benchmarkClient;
        $this->benchmarkRepository = $benchmarkRepository;
    }

    /**
     * @return BenchmarkResponse
     */
    public function transmit()
    {
        $this->benchmarkCollector->get();

        $request = new BenchmarkRequest();
        $request->data = $this->benchmarkCollector->get();

        /** @var BenchmarkResponse $benchmarkResponse */
        $benchmarkResponse = $this->benchmarkClient->sendBenchmark($request);

        $config = $this->benchmarkRepository->getMainConfig();
        $config->setLastSent($benchmarkResponse->getDateUpdated());
        $config->setToken($benchmarkResponse->getToken());

        $this->benchmarkRepository->save($config);

        return $benchmarkResponse;
    }
}
