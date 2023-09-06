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

class Migrations_Migration764 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_addon_premiums
        $sql = <<<'EOD'
            ALTER TABLE `s_addon_premiums`
            MODIFY COLUMN `ordernumber` varchar(255) NOT NULL DEFAULT '0',
            MODIFY COLUMN `ordernumber_export` varchar(255) NOT NULL;
EOD;
        $this->addSql($sql);
    }
}
