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

class Migrations_Migration1206 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        // Try/Catches are necessary to allow multiple runs of this migration
        try {
            $this->connection->exec('ALTER TABLE `s_articles_details` ADD `laststock` INT(1) NOT NULL DEFAULT 0 AFTER `stockmin`');
        } catch (\PDOException $ex) {
            // This code says the column already exists, we want only all other exceptions to be raised
            if ($ex->getCode() !== '42S21') {
                throw $ex;
            }
        }

        try {
            $this->connection->exec('ALTER TABLE `s_article_configurator_options` ADD `media_id` int(11) NULL');
        } catch (\PDOException $ex) {
            // This code says the column already exists, we want only all other exceptions to be raised
            if ($ex->getCode() !== '42S21') {
                throw $ex;
            }
        }

        $this->addSql('UPDATE `s_articles_details` d INNER JOIN `s_articles` a ON a.`id`=d.`articleID` SET d.`laststock`=a.`laststock`');
    }
}
