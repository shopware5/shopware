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

namespace Shopware\Components\HttpCache\InvalidationDate;

use DateTime;

trait InvalidationDateTrait
{
    /**
     * getMostRecentDate sorts an array of DateTime objects and returns the most recent one.
     *
     * @param DateTime[] $dates
     *
     * @return DateTime|null
     */
    protected function getMostRecentDate(array $dates)
    {
        $now = new DateTime();

        // Convert all date-strings into DateTime-objects
        $dates = array_map(function ($el) use ($now) {
            if (empty($el)) {
                return null;
            }
            if (!$el instanceof DateTime) {
                $el = new DateTime($el);
            }

            return $now < $el ? $el : null;
        }, $dates);

        // Exclude empty entries
        $dates = array_filter($dates);

        if (empty($dates)) {
            return null;
        }

        // Pop a date as reference
        $nearest = array_pop($dates);

        // Find the nearest date
        foreach ($dates as $date) {
            if ($now->diff($nearest) < $now->diff($date)) {
                continue;
            }
            $nearest = $date;
        }

        return $nearest;
    }
}
