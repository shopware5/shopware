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

namespace Shopware\Bundle\BenchmarkBundle\Hydrator\TimeHydrator;

use Shopware\Bundle\BenchmarkBundle\Hydrator\LocalHydratorInterface;

abstract class BaseLocalTimeHydrator implements LocalHydratorInterface
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

        $totalValues = [
            'totalOrders' => 0,
            'turnOver' => 0,
            'visitors' => 0,
            'conversions' => 0,
        ];

        foreach ($labels as $label) {
            // Format orders turnOver
            $turnOver = 0;
            $sumOrders = 0;
            if ($hydratedOrders[$label]) {
                $turnOver = $hydratedOrders[$label]['turnOver'];
                $sumOrders = $hydratedOrders[$label]['totalOrders'];
            }

            $hydratedNumbers['turnOver']['values'][] = $turnOver;
            $hydratedNumbers['totalOrders']['values'][] = $sumOrders;
            $totalValues['totalOrders'] += $sumOrders;
            $totalValues['turnOver'] += $turnOver;

            // Format visitors and conversion
            $visitors = 0;
            $conversions = 0;

            if ($hydratedVisitors[$label]) {
                $visitors = $hydratedVisitors[$label]['totalVisitors'];
                $conversions = round($sumOrders / $hydratedVisitors[$label]['totalVisitors'] * 100, 2);
            }

            $hydratedNumbers['visitors']['values'][] = $visitors;
            $hydratedNumbers['conversions']['values'][] = $conversions;

            $totalValues['visitors'] += $visitors;
        }

        if ($totalValues['visitors']) {
            $totalValues['conversions'] = round($totalValues['totalOrders'] / $totalValues['visitors'] * 100, 2) . ' %';
        }

        $totalValues['turnOver'] = number_format($totalValues['turnOver'], 2, ',', '') . ' €';

        $hydratedNumbers['totalValues'] = $totalValues;

        return $hydratedNumbers;
    }

    /**
     * @param array $timeUnits
     * @param array $orders
     *
     * @return array
     */
    protected function hydrateOrders(array $timeUnits, array $orders)
    {
        $hydratedOrders = [];
        foreach ($timeUnits as $unit => $date) {
            if (!$orders[$date]) {
                continue;
            }

            $hydratedOrders[$unit]['turnOver'] += $orders[$date]['orderAmount'];
            $hydratedOrders[$unit]['totalOrders'] += $orders[$date]['totalOrders'];
        }

        return $hydratedOrders;
    }

    /**
     * @param array $timeUnits
     * @param array $visitors
     *
     * @return array
     */
    protected function hydrateVisitors(array $timeUnits, array $visitors)
    {
        $hydratedVisitors = [];
        foreach ($timeUnits as $unit => $date) {
            if (!$visitors[$date]) {
                continue;
            }

            $hydratedVisitors[$unit]['totalVisitors'] += $visitors[$date];
        }

        return $hydratedVisitors;
    }
}
