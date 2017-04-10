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

namespace Shopware\Components\Statistic;

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware_Components_Config as Config;

class StatisticRegistry implements StatisticRegistryInterface
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

    public function __construct(
        array $tracers,
        Config $config,
        BotDetectorInterface $botDetector
    ) {
        $this->tracers = $tracers;
        $this->config = $config;
        $this->botDetector = $botDetector;
    }

    public function traceRequest(Request $request, ShopContextInterface $context): void
    {
        if (!$this->shouldTrace($request)) {
            return;
        }

        foreach ($this->tracers as $tracer) {
            $tracer->traceRequest($request, $context);
        }
    }

    private function shouldTrace(Request $request): bool
    {
        if ($this->botDetector->isBotRequest($request)) {
            return false;
        }
        if ($request->getClientIp() === null) {
            return false;
        }

        $blockedIps = $this->config->get('blockIp');
        $blockedIps = explode(',', $blockedIps);
        $blockedIps = array_filter(array_map('trim', $blockedIps));
        $blockedIps = array_flip($blockedIps);

        if (!empty($blockedIps) && array_key_exists($request->getClientIp(), $blockedIps)) {
            return false;
        }

        return true;
    }
}
