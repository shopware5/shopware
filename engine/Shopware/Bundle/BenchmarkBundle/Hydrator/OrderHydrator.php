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

class OrderHydrator implements LocalHydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $ordersData = $data['orders']['numbers'];

        $ordersData['averageOrdersPerDay'] = $this->getAverageOrders(new \DateTime($ordersData['firstOrderDate']), (int) $ordersData['total']);

        return $ordersData;
    }

    /**
     * @param \DateTime $firstOrderDate
     * @param int       $totalOrders
     *
     * @return float
     */
    private function getAverageOrders($firstOrderDate, $totalOrders)
    {
        $now = new \DateTime('now');

        $daysSinceDate = $firstOrderDate->diff($now)->days;

        return round($totalOrders / $daysSinceDate, 2);
    }
}
