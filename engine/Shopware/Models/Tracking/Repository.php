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

namespace Shopware\Models\Tracking;

use Shopware\Components\Model\ModelRepository;

/**
 * Shopware Tracking Model
 */
class Repository extends ModelRepository
{
    /**
     * Returns an Banner Statistic Model.Either a new one or an existing one. If no date given
     * the current date will be used.
     *
     * @param $bannerId
     * @param \DateTime $date
     *
     * @return Banner
     */
    public function getOrCreateBannerStatsModel($bannerId, \DateTime $date = null)
    {
        if (is_null($date)) {
            $date = new \DateTime();
        }
        $bannerStatistics = $this->findOneBy(['bannerId' => $bannerId, 'displayDate' => $date]);

        // If no Entry for this day exists - create a new one
        if (!$bannerStatistics) {
            $bannerStatistics = new \Shopware\Models\Tracking\Banner($bannerId, $date);

            $bannerStatistics->setClicks(0);
            $bannerStatistics->setViews(0);
        }

        return $bannerStatistics;
    }
}
