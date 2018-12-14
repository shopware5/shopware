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
class Migrations_Migration1450 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $newCountries = [
            'BG' => ['countryName' => 'Bulgarien', 'countryNameEng' => 'Bulgaria', 'countryIso' => 'BG', 'countryEn' => 'BULGARIA', 'iso3' => 'BGR'],
            'EE' => ['countryName' => 'Estland', 'countryNameEng' => 'Estonia', 'countryIso' => 'EE', 'countryEn' => 'ESTONIA', 'iso3' => 'EST'],
            'HR' => ['countryName' => 'Kroatien', 'countryNameEng' => 'Croatia', 'countryIso' => 'HR', 'countryEn' => 'CROATIA', 'iso3' => 'HRV'],
            'LV' => ['countryName' => 'Lettland', 'countryNameEng' => 'Latvia', 'countryIso' => 'LV', 'countryEn' => 'LATVIA', 'iso3' => 'LVA'],
            'LT' => ['countryName' => 'Litauen', 'countryNameEng' => 'Lithuania', 'countryIso' => 'LT', 'countryEn' => 'LITHUANIA', 'iso3' => 'LTU'],
            'MT' => ['countryName' => 'Malta', 'countryNameEng' => 'Malta', 'countryIso' => 'MT', 'countryEn' => 'MALTA', 'iso3' => 'MLT'],
            'SI' => ['countryName' => 'Slowenien', 'countryNameEng' => 'Slovenia', 'countryIso' => 'SI', 'countryEn' => 'SLOVENIA', 'iso3' => 'SVN'],
            'CY' => ['countryName' => 'Zypern', 'countryNameEng' => 'Cyprus', 'countryIso' => 'CY', 'countryEn' => 'CYPRUS', 'iso3' => 'CYP'],
        ];

        $newCountryIsos = array_keys($newCountries);

        $currentCountryStatement = $this->connection->query('SELECT `countryiso` FROM `s_core_countries`;');
        $currentCountryStatement->execute();

        $currentCountryList = $currentCountryStatement->fetchAll(PDO::FETCH_COLUMN);

        $defaultLocaleIdStatement = $this->connection->query('SELECT MID(`locale`, 1, 2) AS localePrefix
            FROM `s_core_locales`
            WHERE `id` = (
                SELECT locale_id
                FROM `s_core_shops`
                WHERE `default` = \'1\'
                LIMIT 1
            )
            LIMIT 1'
        );

        $defaultLocaleIdStatement->execute();
        $defaultLocale = $defaultLocaleIdStatement->fetchColumn();

        $insertStatement = $this->connection->prepare('INSERT INTO `s_core_countries` (`countryname`, `countryiso`, `areaID`, `countryen`, `position`, `notice`, `taxfree`, `taxfree_ustid`, `taxfree_ustid_checked`, `active`, `iso3`, `display_state_in_registration`, `force_state_in_registration`, `allow_shipping`) ' .
            ' VALUES (:countryName, :countryIso, 3, :countryEn, 10, "", 0, 0, 0, 0, :iso3, 0, 0, 1)');

        foreach (array_diff($newCountryIsos, $currentCountryList) as $missingIso) {
            $newCountry = $newCountries[$missingIso];
            $this->saveNewCountry($insertStatement, $newCountry, $defaultLocale);
        }
    }

    /**
     * @param PDOStatement $statement
     * @param array        $newCountry
     * @param string       $defaultLocale
     */
    private function saveNewCountry(PDOStatement $statement, array $newCountry, $defaultLocale)
    {
        if ($defaultLocale === 'en') {
            $newCountry['countryName'] = $newCountry['countryNameEng'];
        }

        unset($newCountry['countryNameEng']);

        $statement->execute($newCountry);
    }
}
