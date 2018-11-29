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
class Migrations_Migration1411 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        /*
         * Column case change does not flush cache and we still get in the constraint upper case column name
         * We need to rename it to a another name and then back
         * @ticket: https://jira.mariadb.org/browse/MDEV-13671
         */
        if ($modus === self::MODUS_UPDATE) {
            return;
        }

        $this->addSql(
            'ALTER TABLE `s_order_documents_attributes` DROP FOREIGN KEY `s_order_documents_attributes_ibfk_1`;'
        );
        $this->addSql(
            'ALTER TABLE `s_order_documents` CHANGE COLUMN `ID` `id_tmp` INT(11) NOT NULL AUTO_INCREMENT FIRST;'
        );
        $this->addSql(
            'ALTER TABLE `s_order_documents` CHANGE COLUMN `id_tmp` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST;'
        );
        $this->addSql(
            'ALTER TABLE `s_order_documents_attributes` ADD CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE;'
        );
    }
}
