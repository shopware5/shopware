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

class MonthlyHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'months';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $daysByWeeks = $this->getDaysByWeekNumber($this->getCurrentWeekOfYear());
        $labels = array_keys($daysByWeeks);

        $hydratedOrders = $this->hydrateOrders($daysByWeeks, $data['orders']);
        $hydratedVisitors = $this->hydrateVisitors($daysByWeeks, $data['visitors']);

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
     * Returns an array like [ 'numberOfWeekInTheYear' => [ 'turnOver' => 'Total turnover for that week', 'totalOrders' => ... ]]
     *
     * @param array $daysByWeeks
     * @param array $orders
     *
     * @return array
     */
    private function hydrateOrders(array $daysByWeeks, array $orders)
    {
        $hydratedOrders = [];
        foreach ($daysByWeeks as $weekNumber => $days) {
            foreach ($days as $day) {
                if (!$orders[$day]) {
                    continue;
                }

                $hydratedOrders[$weekNumber]['turnOver'] += $orders[$day]['orderAmount'];
                $hydratedOrders[$weekNumber]['totalOrders'] += $orders[$day]['totalOrders'];
            }
        }

        return $hydratedOrders;
    }

    /**
     * Returns an array like [ 'numberOfWeekInTheYear' => [ 'totalVisitors' => 'Total visitors for that week' ]]
     *
     * @param array $daysByWeeks
     * @param array $visitors
     *
     * @return array
     */
    private function hydrateVisitors(array $daysByWeeks, array $visitors)
    {
        $hydratedVisitors = [];
        foreach ($daysByWeeks as $weekNumber => $days) {
            foreach ($days as $day) {
                if (!$visitors[$day]) {
                    continue;
                }

                $hydratedVisitors[$weekNumber]['totalVisitors'] += $visitors[$day];
            }
        }

        return $hydratedVisitors;
    }

    /**
     * @return int
     */
    private function getCurrentWeekOfYear()
    {
        $dateTime = new \DateTime('now');

        return (int) $dateTime->format('W');
    }

    /**
     * Returns an array like [ 'numberOfWeekInTheYear' => [ An array containing each date in this week]]
     *
     * @param int $currentWeekNumber
     *
     * @return array
     */
    private function getDaysByWeekNumber($currentWeekNumber)
    {
        $range = range($currentWeekNumber - 3, $currentWeekNumber);

        $days = [];
        foreach ($range as $weekNumber) {
            $weekStart = new \DateTime();
            $now = new \DateTime('now');
            $weekStart->setISODate($now->format('Y'), $weekNumber);

            for ($i = 0; $i < 7; ++$i) {
                $days[$weekNumber][] = $weekStart->format('Y-m-d');
                $weekStart->modify('+1 day');
            }
        }

        return $days;
    }
}
