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

class WeeklyHydrator extends BaseLocalTimeHydrator
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'weeks';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $weekDays = $this->getWeekDays();
        $labels = array_keys($weekDays);

        $hydratedOrders = $this->hydrateOrders($weekDays, $data['orders_graphs']);
        $hydratedVisitors = $this->hydrateVisitors($weekDays, $data['local_visitors']);

        $hydratedData = $this->hydrateWithLabels($labels, $hydratedOrders, $hydratedVisitors);

        $hydratedData['meta'] = $this->fetchMetaData();

        return $hydratedData;
    }

    /**
     * Returns an array like [ 'Friday' => '2018-04-13', 'Saturday' => ... ]
     *
     * @return array
     */
    private function getWeekDays()
    {
        $now = new \DateTime('+1 day');
        $weekAgo = new \DateTime('6 days ago');
        $interval = new \DateInterval('P1D'); // 1 Day interval
        $period = new \DatePeriod($weekAgo, $interval, $now); // 7 Days

        $days = [];
        /** @var \DateTime $day */
        foreach ($period as $day) {
            $days[$day->format('l')] = $day->format('Y-m-d');
        }

        return $days;
    }

    /**
     * @return array
     */
    private function fetchMetaData()
    {
        $now = new \DateTime('now');
        $then = new \DateTime('-6 days');

        return [
            'today' => $now->format('d.m.Y'),
            'shownTime' => $then->format('d.m.') . ' - ' . $now->format('d.m.'),
        ];
    }
}
