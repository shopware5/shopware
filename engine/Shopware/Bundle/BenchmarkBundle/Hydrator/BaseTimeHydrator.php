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

namespace Shopware\Bundle\BenchmarkBundle\Hydrator;

abstract class BaseTimeHydrator implements TimeHydratorInterface
{
    /**
     * @param array $labels
     * @param array $hydratedOrders
     * @param array $hydratedVisitors
     *
     * @return array
     */
    public function hydrateWithLabels(array $labels, array $hydratedOrders, array $hydratedVisitors)
    {
        $hydratedNumbers = [];
        $hydratedNumbers['labels'] = $labels;
        foreach ($labels as $label) {
            // Format orders turnOver
            $turnOver = 0;
            $totalOrders = 0;
            if ($hydratedOrders[$label]) {
                $turnOver = $hydratedOrders[$label]['turnOver'];
                $totalOrders = $hydratedOrders[$label]['totalOrders'];
            }

            $hydratedNumbers['turnOver']['values'][] = $turnOver;
            $hydratedNumbers['totalOrders']['values'][] = $totalOrders;

            // Format visitors and conversion
            $visitors = 0;
            $conversions = 0;

            if ($hydratedVisitors[$label]) {
                $visitors = $hydratedVisitors[$label]['totalVisitors'];
                $conversions = round($totalOrders / $hydratedVisitors[$label]['totalVisitors'] * 100, 2);
            }

            $hydratedNumbers['visitors']['values'][] = $visitors;
            $hydratedNumbers['conversions']['values'][] = $conversions;
        }

        return $hydratedNumbers;
    }
}
