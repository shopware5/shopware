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

use Shopware\Bundle\BenchmarkBundle\BusinessIntelligenceClientInterface;
use Shopware\Bundle\BenchmarkBundle\Struct\BusinessIntelligenceRequest;
use Shopware\Bundle\BenchmarkBundle\Struct\BusinessIntelligenceResponse;
use Shopware\Models\Benchmark\BenchmarkConfig;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class BusinessIntelligenceService
{
    /**
     * @var BusinessIntelligenceClientInterface
     */
    private $biClient;

    /**
     * @var BenchmarkRepository
     */
    private $benchmarkRepository;

    public function __construct(
        BusinessIntelligenceClientInterface $biClient,
        BenchmarkRepository $benchmarkRepository)
    {
        $this->biClient = $biClient;
        $this->benchmarkRepository = $benchmarkRepository;
    }

    /**
     * @return BusinessIntelligenceResponse
     */
    public function transmit(BenchmarkConfig $config)
    {
        /** @var BusinessIntelligenceResponse $response */
        $response = $this->biClient->fetchBusinessIntelligence(new BusinessIntelligenceRequest($config->getToken()));

        $config->setCachedTemplate($response->getHtml());
        $config->setLastReceived($response->getDateTime());

        $this->benchmarkRepository->save($config);

        return $response;
    }
}
