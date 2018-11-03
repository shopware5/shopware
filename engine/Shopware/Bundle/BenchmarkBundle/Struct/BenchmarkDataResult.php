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

namespace Shopware\Bundle\BenchmarkBundle\Struct;

class BenchmarkDataResult
{
    /**
     * @var null|StatisticsResponse
     */
    private $statisticsResponse;

    /**
     * @var null|BusinessIntelligenceResponse
     */
    private $biResponse;

    /**
     * @param null|\Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse           $statisticsResponse
     * @param null|\Shopware\Bundle\BenchmarkBundle\Struct\BusinessIntelligenceResponse $biResponse
     */
    public function __construct(
        StatisticsResponse $statisticsResponse = null,
        BusinessIntelligenceResponse $biResponse = null
    ) {
        $this->statisticsResponse = $statisticsResponse;
        $this->biResponse = $biResponse;
    }

    /**
     * @return null|StatisticsResponse
     */
    public function getStatisticsResponse()
    {
        return $this->statisticsResponse;
    }

    /**
     * @return null|BusinessIntelligenceResponse
     */
    public function getBiResponse()
    {
        return $this->biResponse;
    }
}
