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

class Migrations_Migration799 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->prepare();

        require_once __DIR__ . '/common/AttributeTranslationMigrationHelper.php';
        $helper = new AttributeTranslationMigrationHelper($this->connection);
        $helper->migrate(200000);
    }

    private function prepare()
    {
        $this->connection->exec(<<<EOL
DROP TABLE IF EXISTS translation_migration_id;

CREATE TABLE `translation_migration_id` (
  `max_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOL
        );

        $this->connection->exec('INSERT INTO translation_migration_id (max_id) SELECT MAX(id) as id FROM s_core_translations;');
    }
}
