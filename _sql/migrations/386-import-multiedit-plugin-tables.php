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

class Migrations_Migration386 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Check if the table from the plugin is available
        try {
            $statement = $this->connection->query("SHOW TABLES LIKE 's_plugin_multi_edit_filter'");
            $result = $statement->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return;
        }

        // If not - return
        if (empty($result)) {
            return;
        }

        // Else: Truncate the new filter table and import the existing filters
        $this->addSql('TRUNCATE `s_multi_edit_filter`');

        $sql = <<<'EOD'
            INSERT INTO s_multi_edit_filter (`name`, `filter_string`, `description`, `created`, `is_favorite`, `is_simple`)
            SELECT `name`, `filter_string`, `description`, `created`, `is_favorite`, `is_simple` FROM s_plugin_multi_edit_filter;
EOD;

        $this->addSql($sql);
    }
}
