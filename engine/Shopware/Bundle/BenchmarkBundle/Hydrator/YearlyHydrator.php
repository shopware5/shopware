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

class YearlyHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'years';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $months = $this->getLastMonths();
        $labels = array_keys($months);

        $hydratedOrders = $this->hydrateOrders($months, $data['orders']);
        $hydratedVisitors = $this->hydrateVisitors($months, $data['visitors']);

        $hydratedNumbers = [];
        $hydratedNumbers['labels'] = $labels;
        foreach ($labels as $label) {
            // Format orders turnOver
            if (!$hydratedOrders[$label]) {
                $hydratedNumbers['turnOver']['values'][] = 0;
                $hydratedNumbers['totalOrders']['values'][] = 0;
            } else {
                $hydratedNumbers['turnOver']['values'][] = $hydratedOrders[$label]['turnOver'];
                $hydratedNumbers['totalOrders']['values'][] = $hydratedOrders[$label]['totalOrders'];
            }

            // Format visitors
            if (!$hydratedVisitors[$label]) {
                $hydratedNumbers['visitors']['values'][] = 0;
            } else {
                $hydratedNumbers['visitors']['values'][] = $hydratedVisitors[$label]['totalVisitors'];
            }
        }

        return $hydratedNumbers;
    }

    /**
     * Returns an array like [ 'Dec' => [ 'turnOver' => 'Total turnover for that month', 'totalOrders' => '...' ]]
     *
     * @param array $months
     * @param array $orders
     *
     * @return array
     */
    private function hydrateOrders(array $months, array $orders)
    {
        $hydratedOrders = [];
        foreach ($months as $monthName => $days) {
            foreach ($days as $day) {
                if (!$orders[$day]) {
                    continue;
                }

                $hydratedOrders[$monthName]['turnOver'] += $orders[$day]['orderAmount'];
                $hydratedOrders[$monthName]['totalOrders'] += $orders[$day]['totalOrders'];
            }
        }

        return $hydratedOrders;
    }

    /**
     * Returns an array like [ 'Dec' => [ 'totalVisitors' => 'Total visitors for that month' ]]
     *
     * @param array $months
     * @param array $visitors
     *
     * @return array
     */
    private function hydrateVisitors(array $months, array $visitors)
    {
        $hydratedVisitors = [];
        foreach ($months as $monthName => $days) {
            foreach ($days as $day) {
                if (!$visitors[$day]) {
                    continue;
                }

                $hydratedVisitors[$monthName]['totalVisitors'] += $visitors[$day];
            }
        }

        return $hydratedVisitors;
    }

    /**
     * Returns an array like [ 'Dec' => [ Array containing all dates of that month] ]
     *
     * @return array
     */
    private function getLastMonths()
    {
        $now = new \DateTime('+1 day');
        $yearAgo = new \DateTime('11 months ago');
        $interval = new \DateInterval('P1D'); // 1 day interval
        $period = new \DatePeriod($yearAgo, $interval, $now); // 7 Days

        $months = [];
        /** @var \DateTime $day */
        foreach ($period as $day) {
            $month = substr($day->format('F'), 0, 3);
            $months[$month][] = $day->format('Y-m-d');
        }

        return $months;
    }
}
