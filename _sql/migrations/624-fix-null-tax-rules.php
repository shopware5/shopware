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

class Migrations_Migration624 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $stateSql = <<<SQL
UPDATE s_core_tax_rules tax
INNER JOIN s_core_countries_states state ON state.id = tax.stateID
INNER JOIN s_core_countries country ON country.id = state.countryID
INNER JOIN s_core_countries_areas area ON area.id = country.areaID
SET tax.areaID=area.id, tax.countryID=country.id
WHERE tax.stateID IS NOT NULL;
SQL;

        $this->addSql($stateSql);

        $countrySql = <<<SQL
UPDATE s_core_tax_rules tax
INNER JOIN s_core_countries country ON country.id = tax.countryID
INNER JOIN s_core_countries_areas area ON area.id = country.areaID
SET tax.areaID=area.id
WHERE tax.countryID IS NOT NULL AND tax.stateID IS NULL;
SQL;

        $this->addSql($countrySql);
    }
}
