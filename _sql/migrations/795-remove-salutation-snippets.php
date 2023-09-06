<?php

declare(strict_types=1);
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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration795 extends AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->connection->query('SELECT id, locale_id FROM s_core_shops WHERE `default` = 1 LIMIT 1');
        $shop = $statement->fetchAll(PDO::FETCH_ASSOC);
        $shop = array_shift($shop);
        if (!\is_array($shop)) {
            return;
        }

        $sql = "DELETE FROM s_core_snippets WHERE dirty = 0 AND namespace = 'frontend/salutation' AND value = '' AND (shopID != " . (int) $shop['id'] . ' OR localeID != ' . (int) $shop['locale_id'] . ')';
        $this->addSql($sql);
    }
}
