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

class MonthlyHydrator extends BaseLocalTimeHydrator
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
        $daysByWeeks = $this->getDays();
        $labels = array_keys($daysByWeeks);

        $hydratedOrders = $this->hydrateOrders($daysByWeeks, $data['orders_graphs']);
        $hydratedVisitors = $this->hydrateVisitors($daysByWeeks, $data['local_visitors']);

        $hydratedData = $this->hydrateWithLabels($labels, $hydratedOrders, $hydratedVisitors);

        $hydratedData['meta'] = $this->fetchMetaData();

        return $hydratedData;
    }

    /**
     * @return array
     */
    private function getDays()
    {
        $now = new \DateTime('+1 day');
        $monthAgo = new \DateTime('-4 weeks');
        $interval = new \DateInterval('P1D'); // 1 day interval
        $period = new \DatePeriod($monthAgo, $interval, $now); // 7 Days

        $days = [];
        /** @var \DateTime $day */
        foreach ($period as $day) {
            $days[$day->format('d')] = $day->format('Y-m-d');
        }

        return $days;
    }

    /**
     * @return array
     */
    private function fetchMetaData()
    {
        $now = new \DateTime('now');
        $then = new \DateTime('-3 weeks');

        return [
            'today' => $now->format('W'),
            'shownTime' => $then->format('d.m.') . ' - ' . $now->format('d.m.'),
        ];
    }
}
