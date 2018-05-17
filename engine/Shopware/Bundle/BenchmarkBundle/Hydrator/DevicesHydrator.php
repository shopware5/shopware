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

class DevicesHydrator implements LocalHydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'devices';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(array $data)
    {
        $analyticsData = $data['analytics'];
        $totalVisitors = $analyticsData['totalVisits'];

        $tabletPercentage = 0;
        $mobilePercentage = 0;
        $desktopPercentage = 0;

        if ($totalVisitors) {
            if ($analyticsData['totalVisitsByDevice']['tablet']) {
                $tabletPercentage = round($analyticsData['totalVisitsByDevice']['tablet'] / $totalVisitors * 100, 2);
            }

            if ($analyticsData['totalVisitsByDevice']['mobile']) {
                $mobilePercentage = round($analyticsData['totalVisitsByDevice']['mobile'] / $totalVisitors * 100, 2);
            }

            if ($analyticsData['totalVisitsByDevice']['desktop']) {
                $desktopPercentage = round($analyticsData['totalVisitsByDevice']['desktop'] / $totalVisitors * 100, 2);
            }
        }

        return [
            'tablet' => $tabletPercentage,
            'mobile' => $mobilePercentage,
            'desktop' => $desktopPercentage,
        ];
    }
}
