<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Migrations_Migration920 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // detect if english shop is installed
        $isEnglish = (bool) $this->getConnection()->query('SELECT 1 FROM s_core_units WHERE id = 9 AND unit = "unit" AND description = "Unit"')->rowCount();
        if ($isEnglish) {
            $this->addSql('UPDATE s_core_units SET description = "Linear meter(s)", unit = "lm" WHERE id = 5');

            return;
        }

        $this->addSql('UPDATE s_core_units SET description = "Laufende(r) Meter" WHERE id = 5');
    }
}
