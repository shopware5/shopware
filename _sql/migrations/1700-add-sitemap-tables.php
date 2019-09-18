<?php declare(strict_types = 1);

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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1700 extends AbstractMigration
{
    public function up($modus)
    {
        $customTableSql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS`s_sitemap_custom` (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `url` varchar(512) NOT NULL,
                `priority` int NOT NULL,
                `change_freq` varchar(20) NOT NULL,
                `last_mod` datetime NOT NULL,
                `shop_id` int NULL
            ) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';
SQL;

        $excludeTableSql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS `s_sitemap_exclude` (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `resource` varchar(255) NOT NULL,
                `identifier` LONGTEXT NULL,
                `shop_id` int NULL
            ) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';
SQL;

        $this->addSql($customTableSql);
        $this->addSql($excludeTableSql);

        if ($modus === self::MODUS_UPDATE) {
            $this->migratePreviousConfigurations();
        }
    }

    private function migratePreviousConfigurations(): void
    {
        require_once __DIR__ . '/common/SitemapConfigMigrationHelper.php';
        $helper = new SitemapConfigMigrationHelper($this->connection);
        $helper->migrate();
    }
}
