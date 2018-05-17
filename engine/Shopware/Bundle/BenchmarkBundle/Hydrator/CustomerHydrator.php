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

class CustomerHydrator implements LocalHydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'customers';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $customerData = $data['customers'];

        $customersHydratedData = $this->handleAges($customerData);
        $customersHydratedData = $this->handleGenders($customerData, $customersHydratedData);

        return $customersHydratedData;
    }

    /**
     * @param array $customersData
     *
     * @return array
     */
    private function handleAges(array $customersData)
    {
        $turnOverPerAge = $customersData['turnOverPerAge'];

        $countWithBirthday = 0;
        $countWithoutBirthday = 0;
        $totalAge = 0;
        $totalAmount = 0;
        $above50 = 0;
        $between30And50 = 0;
        $between15And30 = 0;
        foreach ($turnOverPerAge as $birthYear => $amount) {
            $totalAmount += $amount;
            if ($birthYear === 'unknown') {
                $countWithoutBirthday += $amount;
                continue;
            }

            $now = new \DateTime('now');
            $then = \DateTime::createFromFormat('Y', $birthYear);

            $age = $now->diff($then)->y;

            ++$countWithBirthday;
            $totalAge += $age;

            switch ($age) {
                case $age > 50:
                    $above50 += $amount;
                    break;
                case $age > 30 && $age <= 50:
                    $between30And50 += $amount;
                    break;
                case $age > 15 && $age <= 30:
                    $between15And30 += $amount;
                    break;
                default:
                    break;
            }
        }

        $noBirthDayPercentage = round(($countWithoutBirthday / $totalAmount) * 100, 2);
        $above50Percentage = round(($above50 / $totalAmount) * 100, 2);
        $between30And50Percentage = round(($between30And50 / $totalAmount) * 100, 2);
        $between15And30Percentage = round(($between15And30 / $totalAmount) * 100, 2);

        return [
            'averageAge' => is_nan((float) ($totalAge / $countWithBirthday)) ? 0 : (float) ($totalAge / $countWithBirthday),
            'noBirthday' => is_infinite($noBirthDayPercentage) ? 0 : $noBirthDayPercentage,
            'ages' => [
                'above50' => is_infinite($above50Percentage) ? 0 : $above50Percentage,
                'between30And50' => is_infinite($between30And50Percentage) ? 0 : $between30And50Percentage,
                'between15And30' => is_infinite($between15And30Percentage) ? 0 : $between15And30Percentage,
            ],
        ];
    }

    /**
     * @param array $customersData
     * @param array $hydratedData
     *
     * @return array
     */
    private function handleGenders(array $customersData, array $hydratedData)
    {
        $turnOverPerGender = $customersData['turnOverPerGender'];
        $totalAmount = $turnOverPerGender['unknown'] + $turnOverPerGender['male'] + $turnOverPerGender['female'];

        $hydratedData['men'] = [
            'percentage' => ($turnOverPerGender['male'] / $totalAmount) * 100,
            'amount' => $turnOverPerGender['male'] . ' €',
            'averageAge' => $customersData['ageBySex']['averageAgeMen'],
        ];

        $hydratedData['women'] = [
            'percentage' => ($turnOverPerGender['female'] / $totalAmount) * 100,
            'amount' => $turnOverPerGender['female'] . ' €',
            'averageAge' => $customersData['ageBySex']['averageAgeWomen'],
        ];

        return $hydratedData;
    }
}
