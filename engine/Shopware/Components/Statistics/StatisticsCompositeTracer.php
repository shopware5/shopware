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

namespace Shopware\Components\Statistics;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config as Config;

class StatisticsCompositeTracer implements StatisticTracerInterface
{
    /**
     * @var StatisticTracerInterface[]
     */
    private $tracers;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BotDetectorInterface
     */
    private $botDetector;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param StatisticTracerInterface[] $tracers
     * @param Config                     $config
     * @param BotDetectorInterface       $botDetector
     * @param Connection                 $connection
     */
    public function __construct(
        array $tracers,
        Config $config,
        BotDetectorInterface $botDetector,
        Connection $connection
    ) {
        $this->tracers = $tracers;
        $this->config = $config;
        $this->botDetector = $botDetector;
        $this->connection = $connection;
    }

    /**
     * @param Request              $request
     * @param ShopContextInterface $context
     */
    public function trace(Request $request, ShopContextInterface $context)
    {
        if (!$this->shouldTrace($request)) {
            return;
        }

        if ((rand() % 10) == 0) {
            $this->connection->executeUpdate(
                'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)'
            );
            $this->connection->executeUpdate(
                'DELETE FROM s_statistics_pool WHERE datum != CURDATE()'
            );
        }

        foreach ($this->tracers as $tracer) {
            $tracer->trace($request, $context);
        }
    }

    private function shouldTrace(Request $request): bool
    {
        if ($this->botDetector->isBot($request)) {
            return false;
        }
        if ($request->getClientIp() === null) {
            return false;
        }

        $blockedIp = $this->config->get('blockIp');

        if (!empty($blockedIp) && strpos($blockedIp, $request->getClientIp()) !== false) {
            return false;
        }

        return true;
    }
}
