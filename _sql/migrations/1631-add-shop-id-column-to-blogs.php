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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1631 extends AbstractMigration
{
    public function up($modus)
    {
        $exist = $this->connection->query('SHOW COLUMNS FROM `s_blog` LIKE \'shop_ids\';')->fetch();

        if ($exist !== false) {
            return;
        }

        $sql = <<<'EOD'
        ALTER TABLE s_blog ADD COLUMN `shop_ids` varchar(255) NULL;
EOD;
        $this->addSql($sql);
    }
}
