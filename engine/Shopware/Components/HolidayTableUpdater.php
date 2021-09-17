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
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Updates entries in s_premium_holidays to the current date
     *
     * @return void
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
     * @return array<int, string[]>
     */
    private function getPastHolidays(): array
    {
        return $this->db->fetchAllAssociative(
            '
            SELECT id, calculation, `date`
            FROM `s_premium_holidays`
            WHERE `date` < CURDATE()
            '
        );
    }

    /**
     * @param array<int, string[]> $holidays
     */
    private function updateHolidaysForYear(array $holidays, int $year): void
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
            if (!\is_string($calculation)) {
                throw new \RuntimeException('Holiday calculating string could not be created');
            }
            $calculation = str_replace(['EASTERDATE()', 'YEAR()'], ["'$easterDate'", "'$year'"], $calculation);

            $sql = <<<SQL
UPDATE s_premium_holidays
set `date` = $calculation
WHERE id = ?
SQL;
            $this->db->executeStatement($sql, [$id]);
        }
    }

    private function getEasterDateForYear(int $year): string
    {
        if (\function_exists('easter_date')) {
            return date('Y-m-d', easter_date($year));
        }

        return date('Y-m-d', (int) mktime(0, 0, 0, 3, 21 + $this->getEasterDays($year), $year));
    }

    private function getEasterDays(int $year): int
    {
        $G = $year % 19;
        $C = (int) ($year / 100);
        $H = (int) ($C - (int) ($C / 4) - (int) ((8 * $C + 13) / 25) + 19 * $G + 15) % 30;
        $I = (int) $H - (int) ($H / 28) * (1 - (int) ($H / 28) * (int) (29 / ($H + 1)) * ((int) (21 - $G) / 11));
        $J = ($year + (int) ($year / 4) + $I + 2 - $C + (int) ($C / 4)) % 7;
        $L = $I - $J;
        $month = 3 + (int) (($L + 40) / 44);
        $day = (int) ($L + 28 - 31 * ((int) ($month / 4)));
        $E = mktime(0, 0, 0, $month, $day, $year) - mktime(0, 0, 0, 3, 21, $year);

        return (int) round($E / (60 * 60 * 24));
    }
}
