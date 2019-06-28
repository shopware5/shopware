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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;

class HolidayTableUpdater
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Updates entries in s_premium_holidays to the current date
     */
    public function update()
    {
        $date = new \DateTime('now');

        // Update new, or outdated holidays to current year
        $holidays = $this->getPastHolidays();
        if (empty($holidays)) {
            return;
        }

        $currentYear = (int) $date->format('Y');
        $this->updateHolidaysForYear($holidays, $currentYear);

        // Update past holidays to next years date
        $holidays = $this->getPastHolidays();
        if (empty($holidays)) {
            return;
        }

        $nextYear = $currentYear + 1;
        $this->updateHolidaysForYear($holidays, $nextYear);
    }

    /**
     * @return array
     */
    private function getPastHolidays()
    {
        $holidays = $this->db->fetchAll(
            '
            SELECT id, calculation, `date`
            FROM `s_premium_holidays`
            WHERE `date` < CURDATE()
            '
        );

        return $holidays;
    }

    /**
     * @param array $holidays
     * @param int   $year
     */
    private function updateHolidaysForYear($holidays, $year)
    {
        $easterDate = $this->getEasterDateForYear($year);

        foreach ($holidays as $holiday) {
            $id = $holiday['id'];
            $calculation = $holiday['calculation'];

            $calculation = preg_replace(
                "#DATE\('(\d+)[\-/](\d+)'\)#i",
                "DATE(CONCAT(YEAR(),'-','\$1-\$2'))",
                $calculation
            );
            $calculation = str_replace('EASTERDATE()', "'$easterDate'", $calculation);
            $calculation = (string) str_replace('YEAR()', "'$year'", $calculation);

            $sql = <<<SQL
UPDATE s_premium_holidays
set `date` = $calculation
WHERE id = ?
SQL;
            $this->db->executeUpdate($sql, [$id]);
        }
    }

    /**
     * @param int $year
     *
     * @return string
     */
    private function getEasterDateForYear($year)
    {
        $easterDate = date('Y-m-d', mktime(0, 0, 0, 3, 21 + $this->getEasterDays($year), $year));

        return $easterDate;
    }

    /**
     * Wrapper for easter_days function
     *
     * @param int $year
     *
     * @return int
     */
    private function getEasterDays($year)
    {
        if (!function_exists('easter_days')) {
            return $this->easterDaysFallback($year);
        }

        return easter_days($year);
    }

    /**
     * Fallback implementation of easter_days
     *
     * @param int $year
     *
     * @return int
     */
    private function easterDaysFallback($year)
    {
        $G = $year % 19;
        $C = (int) ($year / 100);
        $H = (int) ($C - (int) ($C / 4) - (int) ((8 * $C + 13) / 25) + 19 * $G + 15) % 30;
        $I = (int) $H - (int) ($H / 28) * (1 - (int) ($H / 28) * (int) (29 / ($H + 1)) * ((int) (21 - $G) / 11));
        $J = ($year + (int) ($year / 4) + $I + 2 - $C + (int) ($C / 4)) % 7;
        $L = $I - $J;
        $m = 3 + (int) (($L + 40) / 44);
        $d = $L + 28 - 31 * ((int) ($m / 4));
        $E = mktime(0, 0, 0, $m, $d, $year) - mktime(0, 0, 0, 3, 21, $year);

        return (int) round($E / (60 * 60 * 24));
    }
}
