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

class Migrations_Migration797 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->connection->query('SELECT id, locale_id FROM s_core_shops WHERE `default` = 1 LIMIT 1');
        $shop = $statement->fetchAll(PDO::FETCH_ASSOC);
        $shop = array_shift($shop);

        $exists = $this->connection->query("SELECT id FROM s_core_snippets WHERE namespace = 'frontend/salutation' AND localeID = " . (int) $shop['locale_id'] . ' AND shopID = ' . (int) $shop['id'])->fetch(PDO::FETCH_COLUMN);
        if (!$exists) {
            $this->addSql(
                'UPDATE s_core_snippets SET localeID = ' . (int) $shop['locale_id'] . " WHERE namespace = 'frontend/salutation' AND shopID = " . (int) $shop['id'] . ' AND localeID = ' . (int) $shop['id']
            );
        }

        $sql = "DELETE FROM s_core_snippets WHERE dirty = 0 AND namespace = 'frontend/salutation' AND value = '' AND shopID != " . (int) $shop['id'];
        $this->addSql($sql);

        $sql = "DELETE FROM s_core_snippets WHERE dirty = 0 AND namespace = 'frontend/salutation' AND value = '' AND shopID = " . (int) $shop['id'] . ' AND localeID != ' . (int) $shop['locale_id'];
        $this->addSql($sql);
    }
}
