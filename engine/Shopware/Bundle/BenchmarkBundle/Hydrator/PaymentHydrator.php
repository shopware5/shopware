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

class PaymentHydrator implements LocalHydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'payments';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        return $this->getPaymentUsagePercentage($data['payments']['paymentUsages'], $data['orders']['numbers']['total']);
    }

    /**
     * @param array $paymentUsages
     * @param $totalOrders
     *
     * @return array
     */
    private function getPaymentUsagePercentage(array $paymentUsages, $totalOrders)
    {
        $totalKnownPaymentUsages = 0;

        $percentages = [];
        foreach ($paymentUsages as $paymentUsage) {
            $totalKnownPaymentUsages += (int) $paymentUsage['usages'];

            $percentages[$paymentUsage['name']] = round(((int) $paymentUsage['usages'] / $totalOrders) * 100, 2);
        }

        $unknownPayments = $totalOrders - $totalKnownPaymentUsages;
        if ($unknownPayments) {
            $percentages['unknown'] = round(($unknownPayments / $totalOrders) * 100, 2);
        }

        return $percentages;
    }
}
