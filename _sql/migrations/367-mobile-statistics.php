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

class Migrations_Migration367 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->getConnection()->query(
            "SHOW INDEX FROM s_statistics_article_impression WHERE KEY_NAME = 'articleId_2'"
        );
        $data = $statement->fetchAll();

        if (!empty($data)) {
            $this->addSql(
                'ALTER TABLE `s_statistics_article_impression`
            DROP KEY `articleId_2`;'
            );
        }

        $sql = <<<'EOD'
        ALTER TABLE `s_order` ADD `deviceType` VARCHAR(50) NOT NULL DEFAULT 'desktop';
        ALTER TABLE `s_statistics_visitors` ADD `deviceType` VARCHAR(50) NOT NULL DEFAULT 'desktop';
        ALTER TABLE `s_statistics_currentusers` ADD `deviceType` VARCHAR(50) NOT NULL DEFAULT 'desktop';
        ALTER TABLE `s_statistics_article_impression`
            ADD `deviceType` VARCHAR(50) NOT NULL DEFAULT 'desktop',
            ADD UNIQUE KEY `articleId_2` (`articleId`,`shopId`,`date`, `deviceType`);
EOD;
        $this->addSql($sql);
    }
}
