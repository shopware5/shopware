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

class Migrations_Migration793 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $exists = $this->connection->query("SELECT id FROM s_search_tables WHERE `table` = 's_articles_attributes'");
        $exists = $exists->fetch(PDO::FETCH_COLUMN);

        if ((int) $exists > 0) {
            return;
        }

        $sql = <<<'EOD'
INSERT INTO `s_search_tables` (`id`, `table`, `referenz_table`, `foreign_key`, `where`) VALUES
(NULL, 's_articles_attributes', NULL, NULL, NULL);
EOD;
        $this->addSql($sql);
    }
}
