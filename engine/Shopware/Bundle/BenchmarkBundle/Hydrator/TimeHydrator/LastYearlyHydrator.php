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

class LastYearlyHydrator extends BaseLocalTimeHydrator
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

        $hydratedOrders = $this->hydrateOrders($months, $data['orders_graphs']);
        $hydratedVisitors = $this->hydrateVisitors($months, $data['local_visitors']);

        $hydratedData = $this->hydrateWithLabels($labels, $hydratedOrders, $hydratedVisitors);

        return $hydratedData;
    }

    /**
     * Returns an array like [ 'Dec' => [ Array containing all dates of that month] ]
     *
     * @return array
     */
    private function getLastMonths()
    {
        $now = new \DateTime('+1 day');
        $now->modify('-1 year');
        $yearAgo = new \DateTime('11 months ago');
        $yearAgo->modify('-1 year');
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
